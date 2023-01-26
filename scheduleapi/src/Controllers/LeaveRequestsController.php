<?php

namespace App\Controllers;

use App\Constants\AgentConstants;
use App\Constants\PermissionsConstants;
use App\Entities\VacationRequest;
use App\Functions\AgentsHelper;
use App\Functions\DatesHelper;
use App\Functions\DaysOffHelper;
use App\Functions\EditorsAuth;
use App\Functions\Notifications\reviewNotify;
use App\Functions\SlackNotifications;
use WHMCS\Database\Capsule as DB;
use App\Responses\Response;
use App\Functions\Notifications\daysOffNotify;

class LeaveRequestsController
{
    public function requestslist($request, $response, $args)
    {
        $author =  AgentConstants::adminid();
        $requestAuthor = $args['agentid'] ? $args['agentid'] : $author;
        $results = [];
        $request_type = $_GET['mode'] ? $_GET['mode'] : [];
        $teamsFilter = $_GET['team'];

        $groupsManaging = EditorsAuth::getEditorGroups();

        if (EditorsAuth::isAdmin() || $groupsManaging[4]) {
            $results = DB::table("schedule_vacations_request as r")
                ->join('tbladmins as a', 'a.id', '=', 'r.agent_id')
                ->leftJoin('tbladmins as b', 'b.id', '=', 'r.approve_admin_id')
                ->leftJoin('schedule_agents_to_groups as atg', 'atg.agent_id', '=', 'r.agent_id')
                ->leftJoin('schedule_agentsgroups as ag', 'ag.id', '=', 'atg.group_id');
            if ($request_type) {
                $results->whereIn('r.request_type', explode(',', $request_type));
            }

            if ($groupsManaging[4] && !EditorsAuth::isAdmin()) {

                $results->whereIn('atg.group_id', $groupsManaging[4]);
            }

            if ($teamsFilter) {
                $results->where('atg.group_id', $teamsFilter);
            }

            if (!$_GET['all']) {
                $results->where('r.agent_id', $requestAuthor);
            }

            if (isset($_GET['status']) && $_GET['status'] != '') {
                $results->where('approve_status', $_GET['status']);
            }
            $total = $results->count();

            if ($_GET['order'] && $_GET['orderdir']) {
                $results->orderBy($_GET['order'], $_GET['orderdir']);
            }
            $results = $results->get(['r.*', 'a.firstname as a_firstname', 'a.lastname as a_lastname', 'b.firstname as b_firstname', 'b.lastname as b_lastname', 'ag.group', 'ag.id AS agent_group_id']);
        }

        return Response::json(['response' => 'success', 'data' => $results, 'total' => $total], $response);
    }
    public function submitrequest($request, $response, $args)
    {
        $author =  AgentConstants::adminid();
        $datestart = $request->getParsedBody()['datestart'];
        $dateend = $request->getParsedBody()['dateend'];
        $desc = $request->getParsedBody()['desc'];
        $mode = $request->getParsedBody()['mode'];
        if ($mode == 1) {
            $days_off_count = AgentsHelper::getDaysOffCountByAgent($author);
            $requested_days = count(DatesHelper::generateBetweenDates($datestart, $dateend));
            if ($requested_days > $days_off_count) {
                return Response::json(['response' => 'error', 'msg' => 'You cannot request ' . $requested_days . ' days, because you have ' . $days_off_count . ' days remaining'], $response);
            }
        }
        $results = DB::table("schedule_vacations_request")
            ->insert([
                'agent_id' => $author,
                'date_start' => $datestart,
                'date_end' => $dateend,
                'desc' => $desc,
                'request_type' => $mode,
                'date_submit' => gmdate('Y-m-d H:i:s'),
                'approve_status' => 0,
            ]);

        $slack = new SlackNotifications(new daysOffNotify(['author_id' => $author, 'datestart' => $datestart, 'dateend' => $dateend, 'mode' => $mode]));
        $slack->send();

        return Response::json(['response' => 'success', 'query' => $results], $response);
    }
    public function submitreview($request, $response, $args)
    {
        $groupsManaging = EditorsAuth::getEditorGroups();

        if (!EditorsAuth::isAdmin() && !$groupsManaging[4]) {
            return Response::json(['response' => 'error', 'msg' => 'You dont have permission to do this'], $response);
        }
        $requestEntity = new VacationRequest($args['id']);

        if ($requestEntity->isAlreadyApproved()) {
            return Response::json(['response' => 'error', 'msg' => 'This request has been already handled.'], $response);
        }

        $author =  AgentConstants::adminid();
        $comment = $request->getParsedBody()['comment'];
        $decision = $request->getParsedBody()['decision'] === true ? 1 : 2;

        $excl_dates = $request->getParsedBody()['excl_dates'] ? $request->getParsedBody()['excl_dates'] : [];
        $excl_datesImploded = implode(',', $excl_dates);
        $agent =  $requestEntity->getRow()->agent_id;

        if ($decision === 1 && $requestEntity->getRow()->request_type == 1) {

            $dates = DatesHelper::generateBetweenDates($requestEntity->getRow()->date_start, $requestEntity->getRow()->date_end);

            $daysToSubtract = count($dates) - count($excl_dates);

            DaysOffHelper::AddDaysOffVacations($dates, $agent);
            DaysOffHelper::SubtractDaysFromHolidays($daysToSubtract, $agent);
        }

        $results = DB::table("schedule_vacations_request")->where('id', $args['id'])
            ->update([
                'approve_admin_id' => $author,
                'approve_response' => $comment,
                'approve_date' => gmdate('Y-m-d H:i:s'),
                'approve_status' => $decision,
                'excluded_days' => $excl_datesImploded,
            ]);

        $agent_author = DB::table('tbladmins as a')->whereIn('id', [$author,  $requestEntity->getRow()->agent_id])->get(['a.firstname', 'a.lastname', 'a.id']);
        $requestEntity->setRowAttr('approve_admin_id', $author);
        $requestEntity->setRowAttr('approve_response', $comment);
        $requestEntity->setRowAttr('approve_status', $decision);

        $slack = new SlackNotifications(new reviewNotify(['entries' => $requestEntity, 'admins' => $agent_author]));
        $slack->send();

        return Response::json(['response' => 'success', 'result' => $results], $response);
    }
    public function cancelLeave($request, $response, $args)
    {
        $groupsManaging = EditorsAuth::getEditorGroups();   //TODO: Add checking permission group vs leave request staff group

        if (!EditorsAuth::isAdmin() || !$groupsManaging[4]) {
            return Response::json(['response' => 'error', 'msg' => 'You dont have permission to do this'], $response);
        }
        $entry = DB::table('schedule_vacations_request')->where('id', $args['id'])->first();
        if (!$entry) {
            return Response::json(['response' => 'error', 'msg' => 'Invalid id'], $response);
        }

        $daysCount = DatesHelper::generateBetweenDates($entry->date_start, $entry->date_end);

        if (DaysOffHelper::AddDaysFromHolidays(count($daysCount), $entry->agent_id)) {
            DB::table('schedule_vacations_request')->where('id', $entry->id)->update(['cancelled' => '1']);
            return Response::json(['response' => 'success', 'result' => 'success'], $response);
        }

        return Response::json(['response' => 'error', 'result' => 'Unable to restore days off count'], $response);
    }

    public function reorder($request, $response, $args)
    {
        $id = $request->getParsedBody()['id'];
        $direction = $args['direction'];
        $entry = DB::table('schedule_agentsgroups as ag')
            ->where('id', $id)
            ->first();
        if ($direction == 'up') {
            $entryExchange = DB::table('schedule_agentsgroups as ag')
                ->where('order', $entry->order - 1)
                ->first();

            DB::table('schedule_agentsgroups as ag')
                ->where('id', $entryExchange->id)
                ->update(['order' => $entryExchange->order + 1]);

            DB::table('schedule_agentsgroups as ag')
                ->where('id', $entry->id)
                ->update(['order' => $entry->order - 1]);
            //$entryExchange

            return Response::json(['response' => 'success'], $response);
        } elseif ($direction == 'down') {
            $entryExchange = DB::table('schedule_agentsgroups as ag')
                ->where('order', $entry->order + 1)
                ->first();

            DB::table('schedule_agentsgroups as ag')
                ->where('id', $entryExchange->id)
                ->update(['order' => $entryExchange->order - 1]);

            DB::table('schedule_agentsgroups as ag')
                ->where('id', $entry->id)
                ->update(['order' => $entry->order + 1]);
            //$entryExchange

            return Response::json(['response' => 'success'], $response);
        }
        // $results = DB::table("schedule_agentsgroups as t")
        // ->where('id',$groupid)
        // ->update([$color_field => $color_value]);
    }

    function editLeave($request, $response, $args)
    {
        $groupsManaging = EditorsAuth::getEditorGroups();

        if (!EditorsAuth::isAdmin() || !$groupsManaging[4]) {
            return Response::json(['response' => 'error', 'msg' => 'You dont have permission to perform this action.'], $response);
        }
        $entry = DB::table('schedule_vacations_request as vr')
        ->join('schedule_agents_to_groups as atg', 'atg.agent_id', '=', 'vr.agent_id')
        ->where('vr.id', $args['id'])
        ->first(['vr.agent_id', 'atg.group_id']);
        if (!$entry) {
            return Response::json(['response' => 'error', 'msg' => 'Invalid id'], $response);
        }

        if(!in_array($entry->group_id, $groupsManaging[4]))
        {
            return Response::json(['response' => 'error', 'msg' => 'No permissions to perform this action.'], $response);
        }

        $newdatestart = $request->getParsedBody()['datestart'];
        $newdateend = $request->getParsedBody()['dateend'];

        if ($request->getParsedBody()['diff'] !== 0) {
            //update pool of staff or return error
            $resp = $request->getParsedBody()['diff'] > 0 ? 
                DaysOffHelper::AddDaysFromHolidays($request->getParsedBody()['diff'],$entry->agent_id)
                :
                DaysOffHelper::SubtractDaysFromHolidays(abs($request->getParsedBody()['diff']), $entry->agent_id);

                if(!$resp)
                {
                    return Response::json(['response' => 'error', 'msg' => 'Failed to readjust days in pool.'], $response);
                }
        }

        // update  date range in request

        DB::table('schedule_vacations_request')->where('id', $args['id'])
            ->update(
                [
                    'date_start' =>  $newdatestart,
                    'date_end' => $newdateend,
                    'excluded_days' => implode(',', $request->getParsedBody()['excl_dates'])
                ]
            );

            return Response::json(['response' => 'success'], $response);
    }
}

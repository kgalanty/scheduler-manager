<?php

namespace App\Controllers;

use App\Constants\AgentConstants;
use App\Functions\AgentsHelper;
use App\Functions\DatesHelper;
use App\Functions\DaysOffHelper;
use App\Functions\EditorsAuth;
use WHMCS\Database\Capsule as DB;
use App\Responses\Response;

class LeaveRequestsController
{
    public function requestslist($request, $response, $args)
    {
        $author =  AgentConstants::adminid();
        $requestAuthor = $args['agentid'] ? $args['agentid'] : $author;
        $results = [];
        $request_type = $_GET['mode'] ? (int)$_GET['mode'] : '';

        if ($author == (int)$args['agentid'] || EditorsAuth::isEditor()) {
            $results = DB::table("schedule_vacations_request as r")
                ->join('tbladmins as a', 'a.id', '=', 'r.agent_id')
                ->leftJoin('tbladmins as b', 'b.id', '=', 'r.approve_admin_id')
                ;
            if($request_type)
            {
                $results->where('r.request_type', $request_type);
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
            $results = $results->get(['r.*', 'a.firstname as a_firstname', 'a.lastname as a_lastname', 'b.firstname as b_firstname', 'b.lastname as b_lastname']);
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
        if($mode == 1)
        {
            $days_off_count = AgentsHelper::getDaysOffCountByAgent($author);
            $requested_days = count(DatesHelper::generateBetweenDates($datestart, $dateend));
            if($requested_days > $days_off_count)
            {
                return Response::json(['response' => 'error', 'msg' => 'You cannot request '. $requested_days.' days, because you have '. $days_off_count.' days remaining'], $response);
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

        return Response::json(['response' => 'success', 'query' => $results], $response);
    }
    public function submitreview($request, $response, $args)
    {
        if (!EditorsAuth::isEditor()) {
            return Response::json(['response' => 'error', 'msg' => 'You dont have permission to do this'], $response);
        }
        $author =  AgentConstants::adminid();
        $comment = $request->getParsedBody()['comment'];
        $decision = $request->getParsedBody()['decision'] === true ? 1 : 2;
        $request = DB::table("schedule_vacations_request")->where('id', $args['id'])->first();

        if ($decision === 1 && $request->request_type == 1) {
            
            $dates = DatesHelper::generateBetweenDates($request->date_start, $request->date_end);

            DaysOffHelper::AddDaysOffVacations($dates, $request->agent_id);
        }

        $results = DB::table("schedule_vacations_request")->where('id', $args['id'])
            ->update([
                'approve_admin_id' => $author,
                'approve_response' => $comment,
                'approve_date' => gmdate('Y-m-d H:i:s'),
                'approve_status' => $decision,
            ]);

        return Response::json(['response' => 'success', 'result' => $results], $response);
    }
    public function cancelLeave($request, $response, $args)
    {
        if (!EditorsAuth::isEditor()) {
            return Response::json(['response' => 'error', 'msg' => 'You dont have permission to do this'], $response);
        }
        $entry = DB::table('schedule_vacations_request')->where('id', $args['id'])->first();
        if(!$entry)
        {
            return Response::json(['response' => 'error', 'msg' => 'Invalid id'], $response);
        }

        $daysCount = DatesHelper::generateBetweenDates($entry->date_start, $entry->date_end);

        if(DaysOffHelper::AddDaysFromHolidays(count($daysCount), $entry->agent_id))
        {
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
}

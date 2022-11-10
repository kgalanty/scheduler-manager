<?php

namespace App\Controllers;

use WHMCS\Database\Capsule as DB;
use App\Constants\AgentConstants;
use App\Responses\Response;
use App\Functions\EditorsAuth;
use App\Functions\Logs\AddDaysOff;
use App\Functions\LogsFactory;

class DaysoffController
{
    public function changeDaysOff($request, $response, $args)
    {
        $entryid = (int)$args['entryid'];
        $days = $request->getParsedBody()['changedays'];
        $dateexp = $request->getParsedBody()['dateexp'];
        if ($entryid && $days) {
            DB::table('schedule_daysoff')->where('id', $entryid)->update(['days' => $days, 'date_expiration' => $dateexp]);
            return Response::json(['result' => 'success'], $response);
        }
        return Response::json(['result' => 'error', 'msg' => 'Didnt get all necessary data'], $response);
    }
    public function postDaysOff($request, $response, $args)
    {
        $dateexp = $request->getParsedBody()['dateexp'];
        $daysoff = $request->getParsedBody()['daysoff'];
        $year = (int)$request->getParsedBody()['year'];
        if (DB::table('schedule_daysoff')->where('agent_id', (int)$args['agentid'])->where('year', $year)->count() > 0) {
            return Response::json(['result' => 'error', 'msg' => 'This agent already has days off pool in given year'], $response);
        }
        $r = DB::table('schedule_daysoff')->insert(
            [
                'agent_id' => (int)$args['agentid'],
                'days' => (int)$daysoff,
                'addedby' => (int)$_SESSION['adminid'],
                'created' => date('Y-m-d H:i:s'),
                'date_expiration' => $dateexp, 'year' => $year
            ]
        );
        $log = (new AddDaysOff(['agent_id' => $args['agentid'], 'daysoff' => $daysoff, 'year' => $year]));
        (new LogsFactory($log))->store();
        return Response::json(['result' => $r], $response);
    }
    public function getDaysOff($request, $response, $args)
    {
        $data = [];
        $results = DB::table("schedule_daysoff as d")
            ->where('agent_id', $args['agentid'])
            ->orderBy('d.id', 'DESC')
            ->get();

        return Response::json(['result' => 'success', 'data' => $results], $response);
    }
    public function insertShift($request, $response, $args)
    {
        //$from = date('H:i', strtotime($request->getParsedBody()['from']));
        $from = $request->getParsedBody()['from'];
        $to = $request->getParsedBody()['to'];
        $team = $request->getParsedBody()['team_id'];
        $oncall = $request->getParsedBody()['oncall'];
        if ($oncall) {
            if (DB::table('schedule_shifts')->where(['from' => 'on', 'to' => 'call', 'group_id' => $team])->count() == 0) {
                DB::table('schedule_shifts')->insert(['from' => 'on', 'to' => 'call', 'group_id' => $team]);
                $resp = ['response' => 'success'];
            } else {
                $resp = ['response' => 'This shift already exists'];
            }
        } else {
            if (DB::table('schedule_shifts')->where(['from' => $from, 'to' => $to, 'group_id' => $team])->count() == 0) {
                DB::table('schedule_shifts')->insert(['from' => $from, 'to' => $to, 'group_id' => $team]);
                $resp = ['response' => 'success'];
            } else {
                $resp = ['response' => 'This shift already exists'];
            }
        }
        return Response::json($resp, $response);
        // $response->getBody()->write(json_encode($resp));
        // return $response
        //     ->withHeader('Content-Type', 'application/json');
    }
    public function reorder($request, $response, $args)
    {
        if (!EditorsAuth::isEditor()) {
            $resp = ['response' => 'No permission for this operation'];
        } else {

            $dir = $args['direction'];
            if ($dir == 'up' || $dir == 'down') {
                $id =  $request->getParsedBody()['id'];
                $itemToMove = DB::table('schedule_timetable')->where('id', $id)->first();

                if ($dir == 'up') {
                    $itemToExchange = DB::table('schedule_timetable')->where(
                        [
                            'group_id' => $itemToMove->group_id,
                            'shift_id' => $itemToMove->shift_id,
                            'day' => $itemToMove->day,
                            'draft' => 0,
                            'order' => ($itemToMove->order) - 1
                        ]
                    )->update([
                        'order' => $itemToMove->order
                    ]);
                    DB::table('schedule_timetable')->where('id', $id)->update(['order' => $itemToMove->order - 1]);
                } elseif ($dir == 'down') {
                    $itemToExchange = DB::table('schedule_timetable')->where(
                        [
                            'group_id' => $itemToMove->group_id,
                            'shift_id' => $itemToMove->shift_id,
                            'day' => $itemToMove->day,
                            'draft' => 0,
                            'order' => ($itemToMove->order) + 1
                        ]
                    )->update([
                        'order' => $itemToMove->order
                    ]);
                    DB::table('schedule_timetable')->where('id', $id)->update(['order' => $itemToMove->order + 1]);
                }

                $resp = ['response' => 'success'];
                return Response::json($resp, $response);
            }

            $resp = ['response' => 'Wrong argument'];
        }

        return Response::json($resp, $response);


        // $response->getBody()->write(json_encode($resp));
        // return $response
        //     ->withHeader('Content-Type', 'application/json');
    }
    public function deleteShift($request, $response, $args)
    {
        $id =  $request->getParsedBody()['id'];
        DB::table('schedule_shifts')->where('id', $id)->delete();
        DB::table('schedule_timetable')->where('shift_id', $id)->delete();
        $resp = ['response' => 'success'];
        return Response::json($resp, $response);
        // $response->getBody()->write(json_encode($resp));
        // return $response
        //     ->withHeader('Content-Type', 'application/json');
    }
    public function deleteGroup($request, $response, $args)
    {
        $id =  $request->getParsedBody()['group'];

        DB::table('schedule_agentsgroups')->where('id', $id)->delete();
        DB::table('schedule_agents_to_groups')->where('group_id', $id)->delete();
        DB::table('schedule_shifts')->where('group_id', $id)->delete();
        //This should be handled by constraints in database
        DB::table('schedule_agentsgroups')->where('parent', $id)->delete();
        $resp = ['response' => 'success'];
        return Response::json($resp, $response);
    }
    public function assignToShift($request, $response, $args)
    {
        $data = $request->getParsedBody();
    }
    public function loadDrafts($request, $response, $args)
    {
        $group = (int)$args['groupid'];
        if (!$group) {
            $payload = json_encode(['response' => 'Team not found']);
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }
        $author = AgentConstants::adminid();
        // $shifts = DB::table('schedule_shifts AS s')->where('group_id', $group)
        //     ->get();

        $timetable = DB::table('schedule_timetable AS t')
            ->leftJoin('schedule_timetable_deldrafts as dr',  function ($join) use ($author) {

                $join->on('dr.entry_id', '=', 't.id')->where('dr.author', '=', $author);
            })
            ->leftJoin('tbladmins AS a', 'a.id', '=', 't.agent_id')
            ->leftJoin('schedule_agents_details AS d', 'd.agent_id', '=', 'a.id')
            ->join('schedule_shifts AS shifts', 'shifts.id', '=', 't.shift_id')
            ->where('t.group_id', $group)
            ->where(function ($query) use ($author) {
                $query->where('dr.author', $author);
                $query->orWhere(['t.draft' => 1, 't.author' => $author]);
            })
            ->orderBy('day', 'ASC')
            ->get([
                't.id', 't.shift_id', 't.day', 'a.firstname', 'a.lastname', 'd.color', 'd.bg', 't.draft', 't.author',
                'dr.author AS deldraftauthor',
                'shifts.from', 'shifts.to'
            ]);
        return Response::json(['drafts' => $timetable], $response);
        //     $payload = json_encode(['drafts' => $timetable]);
        // $response->getBody()->write($payload);
        // return $response
        //     ->withHeader('Content-Type', 'application/json');

    }
    public function shiftsGroups($request, $response, $args)
    {
        $author = AgentConstants::adminid();
        $group = $args['groupid'];
        $group = DB::table('schedule_agentsgroups')->where('group', $group)->where('parent', '0')->first();
        if (!$group) {
            $payload = json_encode(['response' => 'Team not found']);
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        $shifts = DB::table('schedule_shifts AS s')->where('group_id', $group->id)->orderBy('s.from', 'ASC')->get();
        $startdate = $_GET['startdate'];
        $startdateparams = explode('-', $startdate, 3);

        $startdateprocessed = date('Y-m-d', strtotime($startdateparams[0] . ' ' . $startdateparams[2]));
        $enddate = date('Y-m-d', strtotime($startdateprocessed . ' +6 days'));
        $timetable = DB::table('schedule_timetable AS t')
            ->leftJoin('schedule_timetable_deldrafts as dr',  function ($join) use ($author) {

                $join->on('dr.entry_id', '=', 't.id')->where('dr.author', '=', $author);
            })
            ->leftJoin('tbladmins AS a', 'a.id', '=', 't.agent_id')
            ->leftJoin('schedule_agents_details AS d', 'd.agent_id', '=', 'a.id')
            ->leftJoin('schedule_agents_to_groups as atg', 'atg.agent_id', '=', 'a.id')
            ->leftJoin('schedule_agentsgroups as ag', 'ag.id', '=', 'atg.group_id')
            ->join('schedule_shifts AS shifts', 'shifts.id', '=', 't.shift_id')
            ->where('t.group_id', $group->id)
            ->whereBetween('t.day', [$startdateprocessed, $enddate])
            ->where(function ($query) use ($author) {
                $query->where('t.draft', '0');
                $query->orWhere(['t.draft' => 1, 't.author' => $author]);
            })
            ->orderBy('t.shift_id')->orderBy('t.order', 'ASC')
            ->get([
                't.id', 't.shift_id', 't.day', 'a.firstname', 'a.lastname', 'd.color', 'd.bg', 't.draft', 't.author',
                'dr.author AS draftauthor',
                'shifts.from', 'shifts.to', 'ag.color as agcolor', 'ag.bgcolor as agbgcolor'
            ]);
        //  echo('<pre>');var_dump($timetable);die;
        $days = [];
        foreach ($timetable as $t) {
            $days[$t->shift_id][$t->day][] = [
                'id' => $t->id,
                'agent' => $t->firstname . ' ' . $t->lastname,
                'color' => $t->agcolor != 'null' ?  $t->agcolor : '#000',
                'bg' => $t->agbgcolor != 'null'  ?  $t->agbgcolor : 'rgb(202 202 202)',
                'author' => $t->author,
                'draft' => $t->draft,
                'deldraftauthor' => $t->draftauthor ?? false,
                'shift' => $t->from . '-' . $t->to,
                'date' => $t->day
            ];
        }
        $data = ['shifts' => $shifts, 't' => $days, 'group' => $group, 'refdate' => $startdateprocessed];
        return Response::json($data, $response);
        // $payload = json_encode($data);
        // $response->getBody()->write($payload);
        // return $response
        //     ->withHeader('Content-Type', 'application/json');
        //$data = $request->getParsedBody();
    }
}

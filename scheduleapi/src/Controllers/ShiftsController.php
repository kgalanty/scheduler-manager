<?php

namespace App\Controllers;

use WHMCS\Database\Capsule as DB;
use App\Constants\AgentConstants;
use App\Responses\Response;

class ShiftsController
{
    public function getShifts($request, $response, $args)
    {
        $data = [];
        $results = DB::table("schedule_agentsgroups as t")
            ->leftJoin('schedule_shifts as s', 's.group_id', '=', 't.id')
            ->get(['s.id', 's.from', 's.to', 't.group', 't.id AS group_id']);
        foreach ($results as $result) {
            // if($result->from && $result->to)
            // $data['shifts'][$result->group_id][] = ['from'=>$result->from, 'to'=>$result->to];
            // $data['groups'][$result->group_id] = ['id' => $result->group_id, 'name' => $result->group];
            if (!$data[$result->group_id]) {

                $shift = $result->from && $result->to ? [['from' => $result->from, 'to' => $result->to,'shiftid' => $result->id]] : [];
                $data[$result->group_id] = ['group_id' => $result->group_id, 'team' => $result->group, 'shifts' => $shift];
            } else {
                $data[$result->group_id]['shifts'][] = ['from' => $result->from, 'to' => $result->to, 'shiftid' => $result->id];
            }
        }
        $result = array_values($data);
        return Response::json($result, $response);
        // $payload = json_encode(array_values($data));
        // $response->getBody()->write($payload);
        // return $response
        //     ->withHeader('Content-Type', 'application/json');
    }
    public function insertShift($request, $response, $args)
    {
        //$from = date('H:i', strtotime($request->getParsedBody()['from']));
        $from = $request->getParsedBody()['from'];
        $to = $request->getParsedBody()['to'];
        $team = $request->getParsedBody()['team_id'];
        $oncall = $request->getParsedBody()['oncall'];
        if($oncall)
        {
            if (DB::table('schedule_shifts')->where(['from' => 'on', 'to' => 'call', 'group_id' => $team])->count() == 0) {
                DB::table('schedule_shifts')->insert(['from' => 'on', 'to' => 'call', 'group_id' => $team]);
                $resp = ['response' => 'success'];
            } else {
                $resp = ['response' => 'This shift already exists'];
            }
        }
        else 
        {
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
        $resp = ['response' => 'success'];
        return Response::json($resp, $response);
        // $response->getBody()->write(json_encode($resp));
        // return $response
        //     ->withHeader('Content-Type', 'application/json');
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
        $shifts = DB::table('schedule_shifts AS s')->where('group_id', $group)
            ->get();

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
        $group = DB::table('schedule_agentsgroups')->where('group', $group)->first();
        if (!$group) {
            $payload = json_encode(['response' => 'Team not found']);
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        $shifts = DB::table('schedule_shifts AS s')->where('group_id', $group->id)->get();
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
            ->join('schedule_shifts AS shifts', 'shifts.id', '=', 't.shift_id')
            ->where('t.group_id', $group->id)
            ->whereBetween('t.day', [$startdateprocessed, $enddate])
            ->where(function ($query) use ($author) {
                $query->where('t.draft', '0');
                $query->orWhere(['t.draft' => 1, 't.author' => $author]);
            })
            ->get([
                't.id', 't.shift_id', 't.day', 'a.firstname', 'a.lastname', 'd.color', 'd.bg', 't.draft', 't.author',
                'dr.author AS draftauthor',
                'shifts.from', 'shifts.to'
            ]);
        $days = [];
        foreach ($timetable as $t) {
            $days[$t->shift_id][$t->day][] = [
                'id' => $t->id, 
                'agent' => $t->firstname . ' ' . $t->lastname, 
                'color' => $t->color ?? '#000', 
                'bg' => $t->bg ?? 'rgb(202 202 202)', 
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

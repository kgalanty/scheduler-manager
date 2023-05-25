<?php

namespace App\Controllers;

use WHMCS\Database\Capsule as DB;
use App\Constants\AgentConstants;
use App\Responses\Response;
use App\Functions\EditorsAuth;
use App\Functions\ShiftsHelper;
use App\Functions\TimetableHelper;

class ShiftsController
{
    public function getShifts($request, $response, $args)
    {
        $data = [];
        $results = DB::table("schedule_agentsgroups as t")
            ->leftJoin('schedule_shifts as s', 's.group_id', '=', 't.id')
            ->orderBy('s.from')
            ->orderBy('t.order', 'ASC')
            ->get(['s.id', 's.from', 's.to', 't.group', 't.color', 't.bgcolor', 't.id AS group_id', 't.parent']);
        // $results2 = DB::table("schedule_agentsgroups as t")
        //     ->leftJoin('schedule_shifts as s', 's.group_id', '=', 't.id')
        //     ->orderBy('s.from')
        //     ->orderBy('t.order', 'ASC')
        //     ->where('s.from', 'LIKE', '00:%')
        //     ->get(['s.id', 's.from', 's.to', 't.group', 't.color', 't.bgcolor', 't.id AS group_id', 't.parent']);

        //    $results = array_merge($results, $results2);
        foreach ($results as $result) {
            // if($result->from && $result->to)
            // $data['shifts'][$result->group_id][] = ['from'=>$result->from, 'to'=>$result->to];
            // $data['groups'][$result->group_id] = ['id' => $result->group_id, 'name' => $result->group];
            if (!$data[$result->group_id]) {
                //add new shift to array
                $shift = $result->from && $result->to ? [['from' => $result->from, 'to' => $result->to, 'shiftid' => $result->id, 'group_id' => $result->group_id]] : [];
                //add main group entry to array
                $data[$result->group_id] = ['group_id' => $result->group_id, 'team' => $result->group, 'shifts' => $shift, 'parent' => $result->parent, 'color' => $result->color, 'bgcolor' => $result->bgcolor];
            } else {
                $data[$result->group_id]['shifts'][] = ['from' => $result->from, 'to' => $result->to, 'shiftid' => $result->id, 'group_id' => $result->group_id];
            }
            if(count($data[$result->group_id]['shifts']) > 0)
            {
                ShiftsHelper::sortShifts($data[$result->group_id]['shifts']);
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
                    $itemToExchange = DB::table('schedule_timetable')
                        ->where(
                            [
                                'group_id' => $itemToMove->group_id,
                                'shift_id' => $itemToMove->shift_id,
                                'day' => $itemToMove->day,
                                'draft' => 0,
                                'order' => ($itemToMove->order) - 1
                            ]
                        )
                        ->update([
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
    public function hideShift($request, $response, $args)
    {
        $shiftid = (int)$args['shiftid'];
        $date = $_GET['refdate'];
        try {
            DB::table('schedule_shifts_hidden')->insert(['shift_id' => $shiftid, 'refdate' => $date]);
            $data = ['result' => 'success'];
        } catch (\Exception $e) {
            $data = ['result' => $e->getMessage()];
        }
        return Response::json($data, $response);
    }
    public function showShift($request, $response, $args)
    {
        $shiftid = (int)$args['shiftid'];
        $date = $_GET['refdate'];
        try {
            DB::table('schedule_shifts_hidden')->where(['shift_id' => $shiftid, 'refdate' => $date])->delete();
            $data = ['result' => 'success'];
        } catch (\Exception $e) {
            $data = ['result' => $e->getMessage()];
        }
        return Response::json($data, $response);
    }
    public function showOnTopbar($request, $response, $args)
    {
        $shiftid = (int)$request->getParsedBody()['shiftid'];
        try {
            DB::table('tblconfiguration')->updateOrInsert(['setting' => 'ScheduleManagerTopBarShift'], ['value' => $shiftid]);
            $data = ['result' => 'success'];
        } catch (\Exception $e) {
            $data = ['result' => $e->getMessage()];
        }
        return Response::json($data, $response);
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

        $startdate = $_GET['startdate'];
        $startdateparams = explode('-', $startdate, 3);

                $enddate = date('Y-m-d',  strtotime($startdateparams[1] . ' ' . $startdateparams[2]));
        $startdateprocessed = date('Y-m-d', strtotime($enddate . ' -6 days'));
        $shifts = DB::table('schedule_shifts AS s')
            ->leftJoin('schedule_shifts_hidden as sh', function ($join) use ($startdateprocessed) {
                $join->on('sh.shift_id', '=', 's.id')->where('sh.refdate', '=', $startdateprocessed);
            })
            ->where('group_id', $group->id)
            ->orderBy('s.from', 'ASC')
            ->get(['s.from', 's.to', 's.id', 's.group_id', DB::raw('COALESCE(sh.hide, 0) as `hide`')]);

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
            ->orderBy('t.shift_id')->orderBy('ag.order', 'ASC')
            ->get([
                't.id', 't.shift_id', 't.day', 'a.firstname', 'a.lastname', 't.draft', 't.author',
                'dr.author AS draftauthor',
                'shifts.from', 'shifts.to', 'ag.color as agcolor', 'ag.bgcolor as agbgcolor', 't.agent_id', 'ag.order as grouporder'
            ]);
        //  echo('<pre>');var_dump($timetable);die;
        $days = [];
        foreach ($timetable as $t) {
            $days[$t->shift_id][$t->day][] = TimetableHelper::renderTimetableRecord($t);
        }
        $shifts = collect($shifts)->map(function($x){ return (array) $x; })->toArray();
        ShiftsHelper::sortShifts($shifts);
        $data = ['shifts' => $shifts, 't' => $days, 'group' => $group, 'refdate' => $startdateprocessed];
        return Response::json($data, $response);
        // $payload = json_encode($data);
        // $response->getBody()->write($payload);
        // return $response
        //     ->withHeader('Content-Type', 'application/json');
        //$data = $request->getParsedBody();
    }
    public function getShowOnTopbar($request, $response, $args)
    {
        $shiftid = DB::table('tblconfiguration')->where('setting', 'ScheduleManagerTopBarShift')->value('value');
        $data = ['response' => 'success', 'shiftid' => (int)$shiftid];
        return Response::json($data, $response);
    }
}

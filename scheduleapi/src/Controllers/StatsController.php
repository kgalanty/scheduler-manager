<?php

namespace App\Controllers;

use App\Functions\DatesHelper;
use WHMCS\Database\Capsule as DB;
use App\Responses\Response;
use App\Functions\ShiftsHelper;

class StatsController
{
    public function get($request, $response, $args)
    {
        if ($_GET['type'] === 'weektickets') {
            $shifts = DB::table("schedule_shifts")->where('from', '07:00')->orWhere('from', '15:00')->orWhere('from', '23:00')->orderBy('from', 'ASC')->get();
            $shiftsCount = count($shifts);

            $datestart = $_GET['datestart'] ? $_GET['datestart'] : date("Y-m-d", strtotime('monday this week'));
            $dateend = $_GET['dateend'] ? $_GET['dateend'] : date("Y-m-d", strtotime('tomorrow'));

            $datesBetween = DatesHelper::generateBetweenDates($datestart, $dateend);

            $earliestTimeBoundary = DatesHelper::convertDateTimeBetweenTimezones($datesBetween[0] . ' ' . $shifts[0]->from . ':00');
            //if shift ending is on next day, add one day to date
            $lastTimeBoundary = DatesHelper::convertDateTimeBetweenTimezones($shifts[count($shifts) - 1]->from > $shifts[$shiftsCount - 1]->to ?
                date('Y-m-d', strtotime($datesBetween[count($datesBetween) - 1] . ' +1 day')) . ' ' . $shifts[$shiftsCount - 1]->to . ':00'
                : $datesBetween[count($datesBetween) - 1] . ' ' . $shifts[$shiftsCount - 1]->to . ':00');


            $datestartQuery = $earliestTimeBoundary;
            $dateendQuery = $lastTimeBoundary;

            $ticketsCreated = DB::table('tbltickets')
                ->whereBetween('date', [$datestartQuery, $dateendQuery])
                ->orderBy('id', 'ASC')
                ->get(['id', 'date', 'admin']);
            $ticketsCreated_counter = ShiftsHelper::divideToShifts($ticketsCreated, $shifts, $datestart, $dateend);

            $ticketRepliesAdmins = DB::table('tblticketreplies')->whereBetween('date', [$datestartQuery, $dateendQuery])->where('admin', '<>', '')->get(['id', 'date', 'admin']);
            $ticketRepliesAdmins_counter = ShiftsHelper::divideToShifts($ticketRepliesAdmins, $shifts, $datestart, $dateend);

            $ticketRepliesCustomers = DB::table('tblticketreplies')->whereBetween('date', [$datestartQuery, $dateendQuery])->where('admin', '=', '')->get(['id', 'date', 'admin']);
            $ticketRepliesCustomers_counter = ShiftsHelper::divideToShifts($ticketRepliesCustomers, $shifts, $datestart, $dateend);

            $results = [
                'date' => $datestart,
                'stats' =>
                [
                    'tickets_created' => $ticketsCreated_counter,
                    'tickets_replies_admins' => $ticketRepliesAdmins_counter,
                    'tickets_replies_customers' => $ticketRepliesCustomers_counter
                ],
                'shifts' => $shifts,
            ];
            return Response::json(['response' => 'success', 'stats' => $results], $response);
        }

        $data = [];
        $from = $_GET['datefrom'];
        $to = $_GET['dateto'];
        $team = (int)$_GET['team'];
        $results = DB::table("schedule_timetable as t")
            ->join('tbladmins as a', 't.agent_id', '=', 'a.id')
            ->leftJoin('schedule_agents_to_groups as atg', 'atg.agent_id', '=', 't.agent_id');
        if ($from) {
            $results = $results->where('t.day', '>=', $from);
        }
        if ($to) {
            $results = $results->where('t.day', '<=', $to);
        }
        if ($team) {
            $results = $results->where('atg.group_id', '=', $team);
        }
        $results = $results->groupBy('t.agent_id')
            ->orderBy('a.firstname', 'ASC')
            ->orderBy('a.lastname', 'ASC')
            //->select(DB::raw('count(t.agent_id) as agent_count, t.day, t.group_id, a.firstname, a.lastname, a.username'))
            ->select(DB::raw('count(distinct t.day) as days, count(t.day) as allshifts, t.agent_id, t.day, a.firstname, a.lastname, a.username'))
            ->get();
        // echo('<pre>');var_dump($results);die;
        // foreach ($results as $result) {
        //     // if($result->from && $result->to)
        //     // $data['shifts'][$result->group_id][] = ['from'=>$result->from, 'to'=>$result->to];
        //     // $data['groups'][$result->group_id] = ['id' => $result->group_id, 'name' => $result->group];
        //     if (!$data[$result->group_id]) {

        //         $shift = $result->from && $result->to ? [['from' => $result->from, 'to' => $result->to,'shiftid' => $result->id]] : [];
        //         $data[$result->group_id] = ['group_id' => $result->group_id, 'team' => $result->group, 'shifts' => $shift];
        //     } else {
        //         $data[$result->group_id]['shifts'][] = ['from' => $result->from, 'to' => $result->to, 'shiftid' => $result->id];
        //     }
        // }
        // $result = array_values($data);
        return Response::json(['response' => 'success', 'stats' => $results], $response);
        // $payload = json_encode(array_values($data));
        // $response->getBody()->write($payload);
        // return $response
        //     ->withHeader('Content-Type', 'application/json');
    }
}

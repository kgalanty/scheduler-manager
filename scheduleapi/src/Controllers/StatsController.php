<?php

namespace App\Controllers;

use WHMCS\Database\Capsule as DB;
use App\Responses\Response;

class StatsController
{
    public function get($request, $response, $args)
    {
        $data = [];
        $from = $request->getQueryParams()['datefrom'];
        $to = $request->getQueryParams()['amp;dateto'];
        $results = DB::table("schedule_timetable as t")
            ->join('tbladmins as a', 't.agent_id', '=', 'a.id');
            if($from)
            {
                $results = $results->where('t.day', '>=', $from);
            }
            if($to)
            {
                $results = $results->where('t.day', '<=', $to);
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

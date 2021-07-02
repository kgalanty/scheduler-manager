<?php

namespace App\Controllers;
use WHMCS\Database\Capsule as DB;
use App\Responses\Response;
class LogsController
{
    public function get($request, $response, $args)
    {
        //$group = (int)$args['groupid'];

    $results = DB::table("schedule_eventslog as et")
        ->join('tbladmins as a', 'a.id', '=', 'et.author')
        ->leftJoin('schedule_agents_details as ad', 'ad.agent_id', '=', 'et.author')
        ->whereBetween('et.date', [$_GET['datefrom'], $_GET['dateto']])
        ->orderBy('et.id', 'DESC')
        ->get(['et.log', 'et.event_date', 'et.action', 'et.path', 'et.date', 'a.firstname', 'a.lastname', 'et.author', 'ad.color', 'ad.bg']);

        return Response::json($results, $response);
        // $payload = json_encode($results);
        // $response->getBody()->write($payload);
        // return $response
        //   ->withHeader('Content-Type', 'application/json');
    }
}

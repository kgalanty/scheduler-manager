<?php

namespace App\Controllers;
use WHMCS\Database\Capsule as DB;
class LogsController
{
    public function get($request, $response, $args)
    {
    $data = [];
    $results = DB::table("schedule_eventslog as et")
        ->join('tbladmins as a', 'a.id', '=', 'et.author')
        ->orderBy('et.id', 'DESC')
        ->limit(20)
        ->get(['et.log', 'et.action', 'et.date', 'a.firstname', 'a.lastname', 'et.author']);

        $payload = json_encode($results);
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}

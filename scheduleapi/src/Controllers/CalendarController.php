<?php

namespace App\Controllers;

use WHMCS\Database\Capsule as DB;
use App\Responses\Response;
use App\Constants\AgentConstants;
use App\Functions\icsHelper;

class CalendarController
{
    public function create($request, $response, $args)
    {
        if(!AgentConstants::adminid()) 
        {
            return Response::json(['response' => 'Not Logged'], $response);
        }
        $existingHash = DB::table('schedule_calendaraccess')->where(
            'agent_id', AgentConstants::adminid())->first();
        if($existingHash)
        {
            $link = 'https://ticketing.stage.tmdhosting.com/schedule/scheduleapi/calendar/'.$existingHash->hash;
            return Response::json(['response' => 'success', 'link' => $link], $response);
        }
        $hash = bin2hex(random_bytes(16));
        DB::table('schedule_calendaraccess')->insert([
            'agent_id' => AgentConstants::adminid(),
            'hash' => $hash
        ]);
        $link = 'https://ticketing.stage.tmdhosting.com/schedule/scheduleapi/calendar/'.$hash;
        return Response::json(['response' => 'success', 'link' => $link], $response);
    }
    public function calendar($request, $response, $args)
    {
        $data = [];
        $agenthash = $request->getQueryParams()['agenthash'];
        if(!$agenthash)
        {
            
        }
        $agent_id = DB::table('schedule_calendaraccess')->where('hash', $agenthash)->value('agent_id');
        $cal = new icsHelper();
        $agent_id = 136;
        $dayStart = date('Y-m-01'); //first day of current month
        $dayEnd = date('Y-m-d', strtotime('sunday next week', time()));
         $schedule = DB::table('schedule_timetable AS t')
         ->join('schedule_shifts AS s', 's.id','=', 't.shift_id')->where('t.agent_id', $agent_id)
         ->join('schedule_agentsgroups AS g', 'g.id','=','t.group_id')
         ->whereBetween('t.day', [$dayStart, $dayEnd])
         ->where('t.draft', '0')
         ->get();
         foreach($schedule as $event)
         {
            $cal->addEvent(function($e) use($event) {
                $e->startDate = new \DateTime($event->day.'T'.$event->from.':00+00:00');
                $e->endDate = new \DateTime($event->day.'T'.$event->to.':00+00:00');
               // $e->uri = 'http://url.to/my/event';
                $e->location = 'TMDHosting';
                $e->description = $event->group;
                $e->summary = $event->group .' @ '.$event->day.' '.$event->from.' - '.$event->to ;
            });
         }
        // $cal->productString = '-//77hz/iFLYER API//';
        
        // $cal->addEvent(function($e) {
        //     $e->startDate = new \DateTime("2021-08-17T11:00:00+02:00");
        //     $e->endDate = new \DateTime("2021-08-17T21:00:00+02:00");
        //     $e->uri = 'http://url.to/my/event';
        //     $e->location = 'Tokyo, Event Location';
        //     $e->description = 'ICS Entertainment';
        //     $e->summary = 'Lorem ipsum dolor ics amet, lorem ipsum dolor ics amet, lorem ipsum dolor ics amet, lorem ipsum dolor ics amet';
        // });

        header('Content-Type: '.icsHelper::MIME_TYPE);
        // if (isset($_GET['download'])) {
        //     header('Content-Disposition: attachment; filename=event.ics');
        // }
        echo $cal->serialize(); exit;
       
       // return Response::json(['response' => 'success', 'stats' => $results], $response);
        
    }
}

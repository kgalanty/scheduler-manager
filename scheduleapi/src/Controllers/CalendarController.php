<?php

namespace App\Controllers;

use WHMCS\Database\Capsule as DB;
use App\Responses\Response;
use App\Constants\AgentConstants;
use App\Functions\icsHelper;
use App\Functions\EditorsAuth;
use App\Constants\Env;

class CalendarController
{
    public function accesslist($request, $response, $args)
    {
        if (!EditorsAuth::isAdmin()) {
            return Response::json(['response' => 'No Permissions to perform this operation'], $response);
        }
        $calendar_list = DB::table('schedule_calendaraccess as c')
            ->leftJoin('tbladmins as a', 'a.id', '=', 'c.agent_id')
            ->get(['c.id', 'a.id AS agent_id', 'a.username', 'a.firstname', 'a.lastname', 'a.disabled', 'c.hash']);
        return Response::json(['response' => 'success', 'calendar' => $calendar_list], $response);
    }

    public function revokeaccess($request, $response, $args)
    {
        $id = (int)$request->getParsedBody()['id'];

        if (!$id) {
            return Response::json(['response' => 'Wrong ID'], $response);
        }

        if (!EditorsAuth::isAdmin()) {
            return Response::json(['response' => 'No permission'], $response);
        }
        DB::table('schedule_calendaraccess')->where('id', $id)->delete();

        return Response::json(['response' => 'success'], $response);
    }

    public function create($request, $response, $args)
    {
        if (!AgentConstants::adminid()) {
            return Response::json(['response' => 'Not Logged'], $response);
        }
        $existingHash = DB::table('schedule_calendaraccess')->where(
            'agent_id',
            AgentConstants::adminid()
        )->first();
        if ($existingHash) {
            $link = Env::api() . '/calendar/' . $existingHash->hash;
            return Response::json(['response' => 'success', 'link' => $link], $response);
        }
        $hash = bin2hex(random_bytes(16));
        DB::table('schedule_calendaraccess')->insert([
            'agent_id' => AgentConstants::adminid(),
            'hash' => $hash
        ]);
        $link = Env::api() . '/calendar/' . $hash;
        return Response::json(['response' => 'success', 'link' => $link], $response);
    }
    public function usercalendar($request, $response, $args)
    {
        $data = [];

        $agenthash = $args['agenthash'];
        if (!$agenthash) {
            return;
        }
        $agent_id = DB::table('schedule_calendaraccess')->where('hash', $agenthash)->value('agent_id');

        $cal = new icsHelper();
        //$agent_id = 136;

        if ((int)DB::table('tbladmins')->where('id', $agent_id)->value('disabled') === 1) {
            die('You have no permission to access this link');
        }

        $dayStart = date('Y-m-01'); //first day of current month
        $dayEnd = date('Y-m-d', strtotime('sunday next week', time()));
        $schedule = DB::table('schedule_timetable AS t')
            ->join('schedule_shifts AS s', 's.id', '=', 't.shift_id')->where('t.agent_id', $agent_id)
            ->join('schedule_agentsgroups AS g', 'g.id', '=', 't.group_id')
            ->whereBetween('t.day', [$dayStart, $dayEnd])
            ->where('t.draft', '0')
            ->get();

        foreach ($schedule as $event) {
            $cal->addEvent(function ($e) use ($event) {
                $e->startDate = new \DateTime($event->day, new \DateTimeZone('Europe/Sofia'));
                $e->endDate = new \DateTime($event->day ,  new \DateTimeZone('Europe/Sofia'));
                //$e->uri = 'http://url.to/my/event';
                $e->location = 'TMDHosting';
                $e->description = $event->group;
                $e->summary = $event->group . ' @ ' . $event->day . ' ' . $event->from . ' - ' . $event->to;
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
        //var_dump( icsHelper::MIME_TYPE);die;
        header('Content-Type: ' . icsHelper::MIME_TYPE);
        header("HTTP/1.1 200 OK");
        // header("Content-type:text/calendar");
        // Header('Content-Length: '.strlen($cal->serialize()));
        // Header('Connection: close');
        // if (isset($_GET['download'])) {
        //     header('Content-Disposition: attachment; filename=event.ics');
        // }
        echo $cal->serialize();
        exit;

        // return Response::json(['response' => 'success', 'stats' => $results], $response);

    }
}

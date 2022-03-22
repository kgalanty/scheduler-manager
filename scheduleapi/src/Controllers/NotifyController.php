<?php

namespace App\Controllers;

use WHMCS\Database\Capsule as DB;
use App\Functions\Logs\AddEntryLog;
use App\Functions\Logs\DeleteEntryLog;
use App\Functions\LogsFactory;
use App\Constants\AgentConstants;
use App\Functions\AgentsHelper;
use App\Functions\EditorsAuth;
use App\Responses\Response;
use App\Functions\Reports;
use App\Functions\DatesHelper;
use App\Functions\ReportsPDFWrapper;
use App\Functions\EmailHelper;
use App\Functions\Logs\DelDaysOff;
use App\Functions\Logs\UseDaysOff;
use App\Functions\Notifications\weekNotify;
use App\Functions\SlackNotifications;

class NotifyController
{
  public function notifyAgents($request, $response, $args)
  {
    //$id = $request->getParsedBody()['id'];
    $groupid = (int)$args['groupid'];
    $date = $args['date'];

    //$agents_id, $entries, [],
    $slack = new SlackNotifications(new weekNotify($groupid, DatesHelper::getWeekRangeBasedOnDay($date)));
    $slack->send();

    //$author = $_SESSION['adminpw'];
    //DB::table("schedule_timetable_deldrafts")->where('entry_id', $id)->where('author', $author)->delete();
    $data['response'] = 'success';
    return Response::json($data, $response);
  }
}

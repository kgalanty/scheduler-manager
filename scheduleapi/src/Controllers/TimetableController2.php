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
use App\Functions\Notifications\commitNotify;
use App\Functions\SlackNotifications;

class TimetableController
{
  public function deleteDraft($request, $response, $args)
  {
    $id = $request->getParsedBody()['id'];
    //$author = $_SESSION['adminpw'];
    $author = AgentConstants::adminid();
    DB::table("schedule_timetable_deldrafts")->where('entry_id', $id)->where('author', $author)->delete();
    $data['response'] = 'success';
    return Response::json($data, $response);
    // $payload = json_encode($data);
    // $response->getBody()->write($payload);
    // return $response
    //   ->withHeader('Content-Type', 'application/json');
  }
  public function deleteVacationing($request, $response, $args)
  {
    $author = AgentConstants::adminid();
    if (!EditorsAuth::isEditor()) {
      $data['response'] = 'No permission for this operation';
    } else {

      $id = $request->getParsedBody()['id'];
      $vacation_day = DB::table('schedule_vacations')->where('id', $id)->first();
      //$today = date('Y-m-d');
      DB::table('schedule_vacations')->where('id', $id)->delete();
      $log = (new DelDaysOff(['agent_id' => $vacation_day->agent_id, 'dayoff' => $vacation_day->day, 'path' => $request->getParsedBody()['path']]));
      (new LogsFactory($log))->store();

      if ($vacation_day->day > date('Y-m-d')) {
        $update_pool = DB::table('schedule_daysoff')
          ->where('agent_id', $vacation_day->agent_id)
          ->where('date_expiration', '>', date('Y-m-d'))
          ->orderBy('year', 'ASC')
          ->first();

        if ($update_pool) {
          DB::table('schedule_daysoff')->where('id', $update_pool->id)->update(['days' => $update_pool->days + 1]);
        }
      } else {
      }

      $data['response'] = 'success';
    }
    return Response::json($data, $response);
  }
  public function deleteDuty($request, $response, $args)
  {
    $author = AgentConstants::adminid();
    if (!EditorsAuth::isEditor()) {
      $data['response'] = 'No permission for this operation';
    } else {

      $id = $request->getParsedBody()['id'];
      $current_entry = DB::table("schedule_timetable")->where('id', $id)->first();
      //-1 is for I need help placeholders
      //add logging
      if (($current_entry->draft == 1 && $current_entry->author == $author) || $current_entry->agent_id == -1) {
        // DB::table('schedule_timetable')->where('id', $id)->update(['draft' => 0, 'author' => 0]);
        DB::table('schedule_timetable')->where('id', $id)->delete();
      } elseif ($current_entry->draft == 0) {
        DB::table('schedule_timetable_deldrafts')->insert(['entry_id' => $id, 'author' => $author]);
      }

      //DB::table("schedule_timetable")->where('id', $id)->delete();
      $data['response'] = 'success';
    }
    return Response::json($data, $response);
  }
  public function insertDuty($request, $response, $args)
  {
    $author =  AgentConstants::adminid();
    if (!EditorsAuth::isEditor()) {
      $data['response'] = 'No permission for this operation';
    } elseif (!$author) {
      $data['response'] = 'You are not logged in. Please log in first.';
    } else {
      $agent = $request->getParsedBody()['agent_id'];
      $shift = $request->getParsedBody()['shift_id'];
      $group = $request->getParsedBody()['group_id'];
      $day = $request->getParsedBody()['date'];

      //check if the agent is available in the same day but other shift, 
      //prevent adding it again
      if ($agent == -1) {
        //Agent -1 is placeholder
        $countForCurrentDay = DB::table("schedule_timetable")->where(
          [
            'group_id' => $group,
            'shift_id' => $shift,
            'day' => $day,
          ]
        )->count();
        DB::table("schedule_timetable")->insert(
          [
            'agent_id' =>  $agent,
            'group_id' => $group,
            'shift_id' => $shift,
            'day' => $day,
            'author' => $author,
            'draft' => 0,
            'order' => ++$countForCurrentDay
          ]
        );
        $data['response'] = 'success';
        return Response::json($data, $response);
      }

      if (DB::table("schedule_timetable")->where([
        'agent_id' =>  $agent,
        'day' => $day,
        'draft' => '0'
      ])->count() > 0 || DB::table("schedule_timetable")->where([
        'agent_id' =>  $agent,
        'day' => $day,
        'draft' => '1',
        'author' => $author
      ])->count() > 0) {
        $data['response'] = 'This Agent has a duty (planned or confirmed) in ' . $day . '.';
        return Response::json($data, $response);
      }
      //check if agent has vacation on the day
      if (DB::table('schedule_vacations')->where([
        'agent_id' => $agent,
        'day' => $day
      ])->count()) {
        $data['response'] = 'This Agent has day off planned for ' . $day . '.';
        return Response::json($data, $response);
      }
      $weekRange = DatesHelper::getWeekRangeBasedOnDay($day);

      if (DB::table("schedule_timetable")->where([
        'agent_id' =>  $agent,
      ])->whereBetween('day', $weekRange)->count() >= 5 && !$request->getParsedBody()['force']) {
        $data['response'] = 'This Agent has already 5 or more shifts this week. Are you sure you wish to add next?';
        $data['action'] = 'Add it anyway';
        return Response::json($data, $response);
      }

      if (DB::table("schedule_timetable")->where([
        'agent_id' =>  $agent,
        'group_id' => $group,
        'shift_id' => $shift,
        'day' => $day
      ])->count() == 0) {
        $countForCurrentDay = DB::table("schedule_timetable")->where(
          [
            'group_id' => $group,
            'shift_id' => $shift,
            'day' => $day,
          ]
        )->count();
        DB::table("schedule_timetable")->insert(
          [
            'agent_id' =>  $agent,
            'group_id' => $group,
            'shift_id' => $shift,
            'day' => $day,
            'author' => $author,
            'draft' => '1',
            'order' => ++$countForCurrentDay
          ]
        );
        $data['response'] = 'success';
      } else {
        $data['response'] = 'Already exist';
      }
    }
    return Response::json($data, $response);
    // $payload = json_encode($data);
    // $response->getBody()->write($payload);
    // return $response
    //   ->withHeader('Content-Type', 'application/json');
  }
  public function commitDrafts($request, $response, $args)
  {
    $notifications = (bool)$request->getParsedBody()['notifications'];
    $author = AgentConstants::adminid();
    if (!EditorsAuth::isEditor()) {
      $data['response'] = 'No permission for this operation';
    } else {
      $sameDaysEntries = DB::table('schedule_timetable')
        ->where('draft', 1)
        ->where('author', $author)
        ->groupBy('day', 'shift_id', 'agent_id')
        ->having('c', '>', '1')
        ->selectRaw('count(*) as c, day')->get();
      if (count($sameDaysEntries) > 0) {
        $data['response'] = 'There is at least one duplicated shift on the same day. Remove it to proceed.';
        return Response::json($data, $response);
      }

      $entries = DB::table('schedule_timetable as t')
        ->join('schedule_shifts as s', 's.id', '=', 't.shift_id')
        ->join('schedule_agentsgroups as ag', 'ag.id', '=', 't.group_id')
        ->where(['t.author' => $author, 't.draft' => 1])
        ->get();
      $deleteentries = DB::table('schedule_timetable_deldrafts as dd')
        ->join('schedule_timetable as t', 't.id', '=', 'dd.entry_id')
        ->join('schedule_shifts as s', 's.id', '=', 't.shift_id')
        ->join('schedule_agentsgroups as ag', 'ag.id', '=', 't.group_id')
        ->where('dd.author', $author)
        ->get(['dd.*', 't.*', 's.from', 's.to', 'ag.group']);

      if ($notifications === true) {


        // $slackUsers = DB::table('schedule_slackusers')->whereIn('agent_id', $agents_id)->get();
        // $slack = new SlackNotifications($slackUsers, $entries, $deleteentries);
        $slack = new SlackNotifications(new commitNotify(['entries' => $entries, 'deleteentries' => $deleteentries]));
        $slack->send();
      }
      foreach ($entries as $entry) {
        //Iterate through draft entries and find if there's placeholder NEED HELP in the same day and shift
        //If it's there, delete one placeholder per one new entry commited
        $placeholder = DB::table('schedule_timetable')
          ->where('group_id', $entry->group_id)
          ->where('shift_id', $entry->shift_id)
          ->where('day', $entry->day)
          ->where('agent_id', '-1')
          ->first();
        if ($placeholder) {
          DB::table('schedule_timetable')->where('id', $placeholder->id)->delete();
        }
      }

      $logs = (new AddEntryLog($entries));
      // $GeneratedLogs = $logs->createAddLogs($entries);
      (new LogsFactory($logs))->store();
      DB::table('schedule_timetable')->where(['author' => $author, 'draft' => 1])->update(['draft' => 0]);


      $logs = (new DeleteEntryLog($deleteentries));
      (new LogsFactory($logs))->store();

      $delid = [];
      foreach ($deleteentries as $delentry) {
        $delid[] = $delentry->id;
      }

      DB::table('schedule_timetable')->whereIn('id', $delid)->delete();
      DB::table('schedule_timetable_deldrafts')->whereIn('entry_id', $delid)->delete();
      $data = ['response' => 'success'];
    }
    return Response::json($data, $response);
    // $payload = json_encode($data);
    // $response->getBody()->write($payload);
    // return $response
    //   ->withHeader('Content-Type', 'application/json');
  }
  public function revertDrafts($request, $response, $args)
  {
    $author = AgentConstants::adminid();
    DB::table('schedule_timetable')->where(
      ['author' => $author, 'draft' => 1]
    )
      ->delete();

    DB::table('schedule_timetable_deldrafts')->where('author', $author)->delete();
    $data = ['response' => 'success'];
    return Response::json($data, $response);
    // $payload = json_encode(['response' => 'success']);
    // $response->getBody()->write($payload);
    // return $response
    //   ->withHeader('Content-Type', 'application/json');
  }
  public function vacationing($request, $response, $args)
  {
    $startdate = $_GET['startdate'];
    $startdateparams = explode('-', $startdate, 3);
    $author = AgentConstants::adminid();

    $startdateprocessed = date('Y-m-d', strtotime($startdateparams[0] . ' ' . $startdateparams[2]));
    $enddateprocessed = date('Y-m-d', strtotime($startdateparams[1] . ' ' . $startdateparams[2]));
    if ($enddateprocessed < $startdateprocessed) {
      $enddateprocessed = date('Y-m-d', strtotime($startdateparams[1] . ' ' . $startdateparams[2] + 1));
    }
    //var_dump($startdateprocessed, $enddateprocessed);die;
    $timetable = DB::table('schedule_vacations AS t')
      ->leftJoin('tbladmins AS a', 'a.id', '=', 't.agent_id')
      ->leftJoin('schedule_agents_details AS d', 'd.agent_id', '=', 'a.id')
      ->leftJoin('schedule_agents_to_groups as ag', 'ag.agent_id', '=', 't.agent_id')
      ->leftJoin('schedule_agentsgroups AS agr', 'agr.id', '=', 'ag.group_id')
      ->whereBetween('t.day', [$startdateprocessed, $enddateprocessed])
      ->where(function ($query) use ($author) {
        $query->where('t.draft', '0');
        $query->orWhere(['t.draft' => 1, 't.author' => $author]);
      })
      ->get([
        't.id',  't.day', 'a.firstname', 'a.lastname', 't.draft', 't.author',
        'agr.group', 'agr.id AS group_id', 'agr.color', 'agr.bgcolor'
      ]);
    $days = [];
    foreach ($timetable as $t) {
      $days[$t->day][] = [
        'id' => $t->id,
        'agent' => $t->firstname . ' ' . $t->lastname,
        'color' => $t->color != 'null' ? $t->color : '#000',
        'bg' => $t->bgcolor != 'null' ? $t->bgcolor : 'rgb(202 202 202)',
        'author' => $t->author,
        'draft' => $t->draft,
        'deldraftauthor' => $t->draftauthor ?? false,
        'group' => $t->group,
        'group_id' => $t->group_id,
        'date' => $t->day
      ];
    }
    $data = ['vacationing' => $days, 'refdate' => $startdateprocessed];
    return Response::json($data, $response);
  }
  public function vacationingStore($request, $response, $args)
  {
    $body =  $request->getParsedBody();

    //Disable storing placeholders in vacations
    if ($body['agent_id'] == -1) {
      return Response::json(['response' => 'Placeholders cannot be stored in vacationing.'], $response);
    }
    if (DB::table('schedule_timetable')->where('agent_id', $body['agent_id'])->where('day', $body['date'])->count() > 0) {
      return Response::json(['response' => 'This agent has already had duty on this date (may be as draft)'], $response);
    }
    if (DB::table("schedule_vacations")->where([
      'agent_id' =>  $body['agent_id'],
      'group_id' => $body['group_id'],
      'day' => $body['date'],
      'draft' => '0',
      'author' => AgentConstants::adminid(),
    ])->count() == 0) {
      DB::table("schedule_vacations")->insert(
        [
          'agent_id' =>  $body['agent_id'],
          'group_id' => $body['group_id'],
          'day' => $body['date'],
          'draft' => '0',
          'author' => AgentConstants::adminid(),
        ]
      );
      $update_pool = DB::table('schedule_daysoff')->where('agent_id', $body['agent_id'])->where('date_expiration', '>', date('Y-m-d'))->orderBy('year', 'ASC')->first();
      if ($update_pool) {
        DB::table('schedule_daysoff')->where('id', $update_pool->id)->update(['days' => $update_pool->days - 1]);
      }
      $log = (new UseDaysOff(['path' => $body['path'], 'agent_id' => $body['agent_id'], 'dayoff' => $body['date']]));
      (new LogsFactory($log))->store();
      $data['response'] = 'success';
    } else {
      $data['response'] = 'Already exist';
    }
    return Response::json($data, $response);
  }
  public function scheduleForWorker($request, $response, $args)
  {
    //
    //$admin = DB::table('tbladmins')->where('id', $args['workerid'])->first(['firstname', 'lastname']);

    $args['workerid'] = $_SESSION['adminid'];
    $reference_group_id = DB::table('schedule_agents_to_groups')
      ->join('schedule_agentsgroups as ag', 'ag.id', '=', 'group_id')
      ->where('agent_id', $args['workerid'])
      ->first();

    $admins = DB::table('schedule_agents_to_groups as g')
      ->join('tbladmins as a', 'a.id', '=', 'g.agent_id')
      //->join('schedule_agents_to_groups as atg', 'atg')
      ->where('g.group_id', $reference_group_id->group_id)
      ->orWhere('g.group_id', $reference_group_id->parent)
      ->get(['g.*', 'a.firstname', 'a.lastname']);
    //->orWhere('g.group_id', function ($query) use ($args) {
    // $query->select('id')->from('schedule_agentsgroups')->where('agent_id', $args['workerid']);
    //})

    //read data for other team members and put into array
    $data = [];
    $reports = new Reports(
      [
        'workers_id' => $admins,
        'startDate' => $args['datestart'],
        'endDate' => $args['dateend']
      ]
    );
    $data = $reports->retrieveReport()->segregateByShiftsDays()->prepareRowsCellsData2();
    $shifts = DB::table('schedule_shifts')->get();

    $dates = DatesHelper::generateBetweenDates($args['datestart'], $args['dateend'], 'D d.m');

    $path = (new ReportsPDFWrapper($admins, ['dates' => $dates, 'cells' => $data[0], 'AllShifts' => $shifts, 'shiftsCells' => $data[1]]))->releasePDF();

    //$sendmessage = 'Message here';
    $systemFromEmail = \WHMCS\Config\Setting::getValue("SystemEmailsFromEmail");
    $adminemail = DB::table('tbladmins')->where('id', $_SESSION['adminid'])->value('email');
    //var_dump($args['datestart'], $args['dateend']);die;
    try {
      $subject = 'Schedule Manager Report - ' . $args['datestart'] . '-' . $args['dateend'];
      $name = 'Name and name';
      $sendmessage = 'Hello, here\'s a generated report in attachment. Have a nice day!';
      $receiver = $adminemail;

      $email = new EmailHelper(\WHMCS\Config\Setting::getValue("SystemEmailsFromName"), $systemFromEmail);
      $email->setSubject($subject);
      $email->setMessage($sendmessage);
      $email->addAttachments([['path' => $path, 'name' => 'report.pdf']]);
      $email->AddAddress($receiver);
      $email->send();
      unlink($path);
    } catch (\PHPMailer\PHPMailer\Exception $e) {
      logActivity("Contact form mail sending failed with a PHPMailer Exception: " . $e->getMessage() . " (Subject: " . $subject . ")");
      return Response::json(['result' => 'error', 'msg' => $e->getMessage()], $response);
    } catch (Exception $e) {
      logActivity("Contact form mail sending failed with this error: " . $e->getMessage());
    }

    return Response::json(['result' => 'success'], $response);
  }
  public function changeOrder($request, $response, $args)
  {
    $author =  AgentConstants::adminid();
    if (!EditorsAuth::isEditor()) {
      $data['response'] = 'No permission for this operation';
    } else {

      $body =  $request->getParsedBody();
      if (DB::table("schedule_vacations")->where([
        'agent_id' =>  $body['agent_id'],
        'group_id' => $body['group_id'],
        'day' => $body['date'],
        'draft' => '0',
        'author' => AgentConstants::adminid(),
      ])->count() == 0) {
        DB::table("schedule_vacations")->insert(
          [
            'agent_id' =>  $body['agent_id'],
            'group_id' => $body['group_id'],
            'day' => $body['date'],
            'draft' => '0',
            'author' => AgentConstants::adminid(),
          ]
        );
        $data['response'] = 'success';
      } else {
        $data['response'] = 'Already exist';
      }
    }
    return Response::json($data, $response);
  }
}

<?php

namespace App\Controllers;

use WHMCS\Database\Capsule as DB;
use App\Functions\Logs\AddEntryLog;
use App\Functions\Logs\DeleteEntryLog;
use App\Functions\LogsFactory;
use App\Constants\AgentConstants;
use App\Functions\EditorsAuth;
use App\Responses\Response;
use App\Functions\Reports;
use App\Functions\DatesHelper;
use App\Functions\ReportsPDFWrapper;
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
  public function deleteDuty($request, $response, $args)
  {
    $author = AgentConstants::adminid();
    if (!EditorsAuth::isEditor()) {
      $data['response'] = 'No permission for this operation';
    } else {

      $id = $request->getParsedBody()['id'];
      $current_entry = DB::table("schedule_timetable")->where('id', $id)->first();
      if ($current_entry->draft == 1 && $current_entry->author == $author) {
        // DB::table('schedule_timetable')->where('id', $id)->update(['draft' => 0, 'author' => 0]);
        DB::table('schedule_timetable')->where('id', $id)->delete();
      } elseif ($current_entry->draft == 0) {
        DB::table('schedule_timetable_deldrafts')->insert(['entry_id' => $id, 'author' => $author]);
      }

      //DB::table("schedule_timetable")->where('id', $id)->delete();
      $data['response'] = 'success';
    }
    return Response::json($data, $response);
    // $payload = json_encode($data);
    // $response->getBody()->write($payload);
    // return $response
    //   ->withHeader('Content-Type', 'application/json');
  }
  public function insertDuty($request, $response, $args)
  {
    $author =  AgentConstants::adminid();
    if (!EditorsAuth::isEditor()) {
      $data['response'] = 'No permission for this operation';
    } else {
      $agent = $request->getParsedBody()['agent_id'];
      $shift = $request->getParsedBody()['shift_id'];
      $group = $request->getParsedBody()['group_id'];
      $day = $request->getParsedBody()['date'];
      if (DB::table("schedule_timetable")->where([
        'agent_id' =>  $agent,
        'group_id' => $group,
        'shift_id' => $shift,
        'day' => $day
      ])->count() == 0) {
        DB::table("schedule_timetable")->insert(
          [
            'agent_id' =>  $agent,
            'group_id' => $group,
            'shift_id' => $shift,
            'day' => $day,
            'author' => $author,
            'draft' => '1'
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
    $author = AgentConstants::adminid();
    if (!EditorsAuth::isEditor()) {
      $data['response'] = 'No permission for this operation';
    } else {

      $entries = DB::table('schedule_timetable')->where(['author' => $author, 'draft' => 1])->get();
      
      $logs = (new AddEntryLog($entries));
     // $GeneratedLogs = $logs->createAddLogs($entries);
      (new LogsFactory($logs))->store();
      DB::table('schedule_timetable')->where(['author' => $author, 'draft' => 1])->update(['draft' => 0]);

      $deleteentries = DB::table('schedule_timetable_deldrafts as dd')
        ->join('schedule_timetable as t', 't.id', '=', 'dd.entry_id')
        ->where('dd.author', $author)
        ->get();
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
        if($enddateprocessed < $startdateprocessed)
        {
          $enddateprocessed = date('Y-m-d', strtotime($startdateparams[1] . ' ' . $startdateparams[2]+1));
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
                't.id',  't.day', 'a.firstname', 'a.lastname', 'd.color', 'd.bg', 't.draft', 't.author',
                'agr.group', 'agr.id AS group_id'
            ]);
        $days = [];
        foreach ($timetable as $t) {
            $days[$t->day][] = [
                'id' => $t->id, 
                'agent' => $t->firstname . ' ' . $t->lastname, 
                'color' => $t->color ?? '#000', 
                'bg' => $t->bg ?? 'rgb(202 202 202)', 
                'author' => $t->author, 
                'draft' => $t->draft, 
                'deldraftauthor' => $t->draftauthor ?? false,
                'group' => $t->group,
                'group_id'=> $t->group_id,
                'date' => $t->day
            ];
        }
        $data = ['vacationing' => $days, 'refdate' => $startdateprocessed];
        return Response::json($data, $response);
  }
  public function scheduleForWorker($request, $response, $args)
  {
   //
   //$admin = DB::table('tbladmins')->where('id', $args['workerid'])->first(['firstname', 'lastname']);
  
   $args['workerid'] = $_SESSION['adminid'];
   $admins = DB::table('schedule_agents_to_groups as g')
   ->join('tbladmins as a', 'a.id', '=', 'g.agent_id')
   ->where('g.group_id', function($query) use($args)
   {
    $query->select('group_id')->from('schedule_agents_to_groups')->where('agent_id', $args['workerid']);
   })
   ->get(['g.*', 'a.firstname', 'a.lastname']);
 
    //read data for other team members and put into array
    $data = [];
    $reports = new Reports(
      ['workers_id' => $admins, 
      'startDate' => $args['datestart'],
      'endDate' => $args['dateend']]);
    $data = $reports->retrieveReport()->segregateByShiftsDays()->prepareRowsCellsData2();
    $shifts = DB::table('schedule_shifts')->get();

    $dates = DatesHelper::generateBetweenDates($args['datestart'], $args['dateend'], 'D d.m');

    new ReportsPDFWrapper($admins, ['dates' => $dates, 'cells' => $data[0], 'AllShifts' => $shifts, 'shiftsCells' => $data[1]]);
  }
}

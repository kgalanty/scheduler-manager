<?php

namespace App\Controllers;

use WHMCS\Database\Capsule as DB;
use App\Functions\LogEntry as Logs;
use App\Constants\AgentConstants;
use App\Functions\DatesHelper;
use App\Responses\Response;

class TemplatesController
{
  public function confirm($request, $response, $args)
  {
    $tplid = (int)$request->getParsedBody()['id'];
    $dates = $request->getParsedBody()['date'];
    $overwrite = (bool)$request->getParsedBody()['overwrite'];

    $datesParsed = DatesHelper::DatesFromRoute($dates);
    if (!$datesParsed) {
      $data['response'] = 'Error, date format incorrect.';
    } else {
      // $startdateparams = explode('-', $dates, 3);
      $startdateprocessed = $datesParsed[0];
      $enddate = $datesParsed[1];

      $template_entries = DB::table('schedule_templates as t')
        ->join('schedule_tplshifts as ts', 'ts.tpl_id', '=', 't.id')->where('t.id', $tplid)->get();
      $logs = (new Logs());
      $logs->createTplConfirmLog($tplid, $datesParsed, $overwrite);
      if ($overwrite) {
        foreach ($template_entries as $e) {
          $shiftsToDelete[] = $e->shift_id;
        }
        $entries = DB::table('schedule_timetable')->whereIn('shift_id', $shiftsToDelete)->whereBetween('day', [$startdateprocessed, $enddate])->where('draft', '0')->get();

        $logs->createDelLogs($entries);

        DB::table('schedule_timetable')->whereIn('shift_id', $shiftsToDelete)->whereBetween('day', [$startdateprocessed, $enddate])->where('draft', '0')->delete();
      }
      foreach ($template_entries as $e) {
        $shiftsToAdd[] = ['agent_id' => $e->agent_id, 'group_id' => $e->group_id, 'shift_id' => $e->shift_id, 'day' => DatesHelper::getDateBasedOnWeekday((int)$e->day, $startdateprocessed), 'draft' => '1', 'author' => AgentConstants::adminid()];
      }
      $logs->createAddLogs($shiftsToAdd);
      DB::table('schedule_timetable')->insert($shiftsToAdd);
      //var_dump($template_entries);
      //die;
      //$id = $request->getParsedBody()['agent_id'];
      //$author = $_SESSION['adminpw'];
      // $author = AgentConstants::adminid();
      //DB::table("schedule_editors")->where('editor_id', $id)->delete();
      $data['response'] = 'success';
    }
    return Response::json($data, $response);
    // $payload = json_encode($data);
    // $response->getBody()->write($payload);
    // return $response
    //   ->withHeader('Content-Type', 'application/json');
  }
  public function delete($request, $response, $args)
  {
    $id = (int)$request->getParsedBody()['id'];
    if ($id > 0) {
      DB::table("schedule_templates")->where('id', $id)->delete();
      DB::table("schedule_tplshifts")->where('tpl_id', $id)->delete();
      $data['response'] = 'success';
    } else {
      $data['response'] = 'Wrong template ID';
    }
    //$author = $_SESSION['adminpw'];
    // $author = AgentConstants::adminid();

    return Response::json($data, $response);
    // $payload = json_encode($data);
    // $response->getBody()->write($payload);
    // return $response
    //   ->withHeader('Content-Type', 'application/json');
  }
  public function list($request, $response, $args)
  {
    $group_id = (int)$args['groupid'];

    $list = DB::table("schedule_templates as t")
      ->join('schedule_tplshifts as ts', 'ts.tpl_id', '=', 't.id')
      ->join('schedule_shifts as s', 's.id', '=', 'ts.shift_id')
      ->join('tbladmins as a', 'a.id', '=', 'ts.agent_id')
      ->where('t.group_id', $group_id)
      ->orderBy('ts.shift_id')
      ->orderBy('ts.day', 'ASC')
      ->get(['t.*', 'ts.day', 'ts.shift_id', 's.from', 's.to', 'a.firstname', 'a.lastname']);
    if ($list) {
      foreach ($list as $listitem) {
        $resp[$listitem->id][] = $listitem;
      }
    }
    $data = ['response' => 'success', 'list' => $resp];
    return Response::json($data, $response);
    // $response->getBody()->write(json_encode(['response' => 'success', 'list' => $resp]));
    // return $response
    //   ->withHeader('Content-Type', 'application/json');
  }
  public function add($request, $response, $args)
  {
    $author =  AgentConstants::adminid();

    $date = $request->getParsedBody()['date'];
    $group_id = (int)$request->getParsedBody()['group_id'];
    $name = $request->getParsedBody()['name'];
    $shift_id = $request->getParsedBody()['shift_id'];
    $end_date = date('Y-m-d', strtotime($date . ' +6 days'));

    if (DB::table('schedule_templates')->where('name', $name)->count() > 0) {
      $data['response'] = 'This name is already taken';
    } else {
      $entries = DB::table('schedule_timetable')
        ->where('group_id', $group_id)
        ->whereBetween('day', [$date, $end_date]);
      if ($shift_id != 'All') {
        $entries = $entries->where('shift_id', $shift_id);
      }
      $count = $entries->count();
      if ($count == 0) {
        $data['response'] = 'This week doesnt have any shifts/staff assigned';
      } else {
        $entries = $entries->get();
        $new_entries = [];
        $new_id_tpl = DB::table('schedule_templates')->insertGetId(['name' => $name, 'group_id' => $group_id]);
        foreach ($entries as $e) {
          $new_entries[] = ['tpl_id' => $new_id_tpl, 'day' => date('N', strtotime($e->day)), 'agent_id' => (int)$e->agent_id, 'shift_id' => (int)$e->shift_id];
        }
        DB::table('schedule_tplshifts')->insert($new_entries);
        $data['response'] = 'success';
      }
    }
    //$data['response'] = 'Already exist';
    return Response::json($data, $response);
    // $payload = json_encode($data);
    // $response->getBody()->write($payload);
    // return $response
    //   ->withHeader('Content-Type', 'application/json');
  }
}

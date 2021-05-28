<?php

namespace App\Controllers;

use WHMCS\Database\Capsule as DB;
use App\Functions\LogEntry as Logs;
use App\Constants\AgentConstants;
use App\Functions\EditorsAuth;

class TimetableController
{
  public function deleteDraft($request, $response, $args)
  {
    $id = $request->getParsedBody()['id'];
    //$author = $_SESSION['adminpw'];
    $author = AgentConstants::adminid();
    DB::table("schedule_timetable_deldrafts")->where('entry_id', $id)->where('author', $author)->delete();
    $data['response'] = 'success';
    $payload = json_encode($data);
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
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
    $payload = json_encode($data);
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
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
    $payload = json_encode($data);
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  public function commitDrafts($request, $response, $args)
  {
    $author = AgentConstants::adminid();
    if (!EditorsAuth::isEditor()) {
      $data['response'] = 'No permission for this operation';
    } else {

      $entries = DB::table('schedule_timetable')->where(['author' => $author, 'draft' => 1])->get();
      $logs = (new Logs());
      $logs->createAddLogs($entries);
      DB::table('schedule_timetable')->where(['author' => $author, 'draft' => 1])->update(['draft' => 0]);

      $deleteentries = DB::table('schedule_timetable_deldrafts as dd')
        ->join('schedule_timetable as t', 't.id', '=', 'dd.entry_id')
        ->where('dd.author', $author)
        ->get();
      $logs->createDelLogs($deleteentries);

      $delid = [];
      foreach ($deleteentries as $delentry) {
        $delid[] = $delentry->id;
      }

      DB::table('schedule_timetable')->whereIn('id', $delid)->delete();
      DB::table('schedule_timetable_deldrafts')->whereIn('entry_id', $delid)->delete();
      $data = ['response' => 'success'];
    }
    $payload = json_encode($data);
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  public function revertDrafts($request, $response, $args)
  {
    $author = AgentConstants::adminid();
    DB::table('schedule_timetable')->where(
      ['author' => $author, 'draft' => 1]
    )
      ->delete();

    DB::table('schedule_timetable_deldrafts')->where('author', $author)->delete();
    $payload = json_encode(['response' => 'success']);
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
}

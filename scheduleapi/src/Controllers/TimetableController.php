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
  public function scheduleForWorker($request, $response, $args)
  {
   //
   //$admin = DB::table('tbladmins')->where('id', $args['workerid'])->first(['firstname', 'lastname']);

   $admins = DB::table('schedule_agents_to_groups as g')
   ->join('tbladmins as a', 'a.id', '=', 'g.agent_id')
   ->where('g.group_id', function($query) use($args)
   {
    $query->select('group_id')->from('schedule_agents_to_groups')->where('agent_id', $args['workerid']);
   })
   ->get(['g.*', 'a.firstname', 'a.lastname']);
  
    //read data for other team members and put into array
    $dataOfDatas = [];
   foreach($admins as $admin)
   {
    $reports = new Reports(
      ['worker_id' => $admin->agent_id, 
      'startDate' => $args['datestart'],
      'endDate' => $args['dateend']]);
    $dataOfDatas[] = $reports->retrieveReport()->segregateByDays()->prepareRowsCellsData();
    }
    //echo('<pre>');  var_dump($dataOfDatas); die;
    //echo('<pre>');  var_dump($output); die;
    //$header = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
    $dates = DatesHelper::generateBetweenDates($args['datestart'], $args['dateend'], 'D d.m');
    new ReportsPDFWrapper($admins, ['dates' => $dates, 'cells' => $dataOfDatas]);

    // $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);                 // create TCPDF object with default constructor args
    //   $pdf->AddPage();                    // pretty self-explanatory

    //   $pdf->writeHTML('<h2>'.$admin->firstname.' '.$admin->lastname.'</h2>', true, false, true, false, '');
    //   $pdf->writeHTML('<h3>'.$args['datestart'].' - '.$args['dateend'].'</h3>', true, false, true, false, '');
    
    //   $pdf->SetFillColor(255, 0, 0);
    //     $pdf->SetTextColor(255);
    //     $pdf->SetDrawColor(128, 0, 0);
    //     $pdf->SetLineWidth(0.3);
    //     $pdf->SetFont('', 'B');
    //     // Header
    //     $w = 28;

    //     $num_headers = count($header);
    //     for($i = 0; $i < $num_headers; ++$i) {
    //         $pdf->Cell($w, 7, $dates[$i], 1, 0, 'C', 1);
    //     }
    //     $pdf->Ln();
    //     $pdf->SetFillColor(224, 235, 255);
    //     $pdf->SetTextColor(0);
    //     $pdf->SetFont('');
    //     $fill = 0;
    //     foreach($data as $row) {
    //       for($i = 0; $i < $num_headers; ++$i) {
    //         $pdf->Cell($w, 7, $row[$i], 1, 0, 'C', $fill);
    //     }
    //       $pdf->Ln();
    //       $fill=!$fill;
    //   }
    //     // Color and font restoration
    //     $pdf->SetFillColor(224, 235, 255);
    //     $pdf->SetTextColor(0);
    //     $pdf->SetFont('');

    //   $pdf->Output('report.pdf'); 
  }
}

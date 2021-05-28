<?php

namespace App\Controllers;

use WHMCS\Database\Capsule as DB;
use App\Functions\LogEntry as Logs;
use App\Constants\AgentConstants;

class EditorsController
{
  public function delete($request, $response, $args)
  {
    $id = $request->getParsedBody()['agent_id'];
    //$author = $_SESSION['adminpw'];
   // $author = AgentConstants::adminid();
    DB::table("schedule_editors")->where('editor_id', $id)->delete();
    $data['response'] = 'success';
    $payload = json_encode($data);
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  public function list($request, $response, $args)
  {
    $list = DB::table("schedule_editors as e")->join('tbladmins as a', 'a.id', '=', 'e.editor_id')
    ->get(['a.id', 'a.firstname', 'a.lastname', 'a.username']);
    $response->getBody()->write(json_encode(['response'=>'success', 'list' => $list]));
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  public function add($request, $response, $args)
  {
    $author =  AgentConstants::adminid();

      $agent = $request->getParsedBody()['agent_id'];
     
      if (DB::table("schedule_editors")->where([
        'editor_id' =>  $agent,
       
      ])->count() == 0) {
        DB::table("schedule_editors")->insert(
          [
            'editor_id' =>  $agent,
          ]
        );
        $data['response'] = 'success';
      } else {
        $data['response'] = 'Already exist';
      }
    $payload = json_encode($data);
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
}

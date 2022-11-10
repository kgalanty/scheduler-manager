<?php

namespace App\Controllers;

use WHMCS\Database\Capsule as DB;
use App\Functions\LogEntry as Logs;
use App\Constants\AgentConstants;
use App\Responses\Response;

class PermissionsController
{
  public function setPermissions($request, $response, $args)
  {
    $agentid = $args['agentid'];
    $group_id = (int)$request->getParsedBody()['group_id'];
    $perm =  (int)$request->getParsedBody()['perm'];

    try {
      if (DB::table('schedule_agents_groups_editor')->where('agent_id', $agentid)
        ->where('group_id', $group_id)->where('permission', $perm)->count() == 0
      ) {
        DB::table('schedule_agents_groups_editor')->insert([
          'agent_id' => $agentid,
          'group_id' => $group_id, 'permission' => $perm
        ]);
      } else {
        DB::table('schedule_agents_groups_editor')->where('agent_id', $agentid)
          ->where('group_id', $group_id)->where('permission', $perm)->delete();
      }
      $data = ['result' => 'success'];
    } catch (\Exception $e) {
      $data = ['result' => 'error', 'msg' => $e->getMessage()];
    }
    return Response::json($data, $response);
  }
  public function getPermissions($request, $response, $args)
  {
    $agentid = $args['agentid'];

    try {
      $permissions = DB::table('schedule_agents_groups_editor as e')
        ->where('e.agent_id', $agentid)
        ->get(['e.group_id', 'e.permission']);

      // $permsOut = [];
      // foreach ($permissions as $perm) {
      //   $permsOut[$perm->group_id][(int)$perm->permission] = true;
      // }

      $data['response'] = 'success';
    } catch (\Exception $e) {
      $data['response'] = 'error';
      $data['msg'] = $e->getMessage();
    }

    $data['permissions'] = $permissions;
    $data['agent_id'] = $agentid;

    return Response::json($data, $response);
  }


  public function delete($request, $response, $args)
  {
    $id = $request->getParsedBody()['agent_id'];
    //$author = $_SESSION['adminpw'];
    // $author = AgentConstants::adminid();
    DB::table("schedule_editors")->where('editor_id', $id)->delete();
    $data['response'] = 'success';
    return Response::json($data, $response);
    // $payload = json_encode($data);
    // $response->getBody()->write($payload);
    // return $response
    //   ->withHeader('Content-Type', 'application/json');
  }
  public function list($request, $response, $args)
  {
    $list = DB::table("schedule_editors as e")->join('tbladmins as a', 'a.id', '=', 'e.editor_id')
      ->get(['a.id', 'a.firstname', 'a.lastname', 'a.username']);
    $data = ['response' => 'success', 'list' => $list];
    return Response::json($data, $response);
    // $response->getBody()->write(json_encode(['response'=>'success', 'list' => $list]));
    // return $response
    //   ->withHeader('Content-Type', 'application/json');
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
    return Response::json($data, $response);
    // $payload = json_encode($data);
    // $response->getBody()->write($payload);
    // return $response
    //   ->withHeader('Content-Type', 'application/json');
  }
}

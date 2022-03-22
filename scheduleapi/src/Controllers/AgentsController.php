<?php

namespace App\Controllers;

use WHMCS\Database\Capsule as DB;
use App\Constants\AgentConstants;
use App\Functions\DatesHelper;
use App\Functions\EditorsAuth;
use App\Responses\Response;

class AgentsController
{
  public function agents($request, $response, $args)
  {
    $data = DB::table("tbladmins as a")->where('a.disabled', '0')
      ->leftJoin('schedule_agents_details AS ad', 'ad.agent_id', '=', 'a.id')
      ->leftJoin('schedule_agents_to_groups AS ag', 'ag.agent_id', '=', 'a.id')
      ->leftJoin('schedule_agentsgroups AS agg', 'ag.group_id', '=', 'agg.id')
      ->orderBy('a.firstname', 'ASC')
      ->get(['a.id', 'a.firstname', 'a.lastname', 'a.username', 'ad.color', 'ad.bg', 'agg.id AS groupid', 'agg.group']);
    $row = [];
    foreach ($data as $d) {
      $row[$d->id] = (array)$d;
      $row[$d->id]['fullrow'] = '#' . $d->id . ' ' . $d->firstname . ' ' . $d->lastname . ' (' . $d->username . ')';
      $row[$d->id]['groups'][] = ['name' => $d->group, 'id' => $d->groupid];
    }
    $row = array_values($row);
    return Response::json($row, $response);
    // $payload = json_encode($row);
    // $response->getBody()->write($payload);
    // return $response
    //   ->withHeader('Content-Type', 'application/json');
  }
  public function addGroup($request, $response, $args)
  {
    $name =  $request->getParsedBody()['name'];
    $agents =  $request->getParsedBody()['agents'];
    $parent =  $request->getParsedBody()['parent'];
    if (!$groupid = DB::table('schedule_agentsgroups')->where(['group' => $name, 'parent' => $parent])->value('id')) {
      $groupid = DB::table('schedule_agentsgroups')->insertGetId(['group' => $name, 'parent' => $parent]);
      $payload = ['response' => 'success'];
    } else {
      $payload = ['response' => 'This name already exists'];
    }
    // //$groupid = DB::table('schedule_agentsgroups')->insertGetId(['group' => $name]);
    // $rows = [];
    // foreach ($agents as $agent) {
    //   $rows[] = ['group_id' => $groupid, 'agent_id' => $agent];
    // }
    // if ($rows)
    //   DB::table('schedule_agents_to_groups')->insert($rows);
    return Response::json($payload, $response);
    // $response->getBody()->write(json_encode($resp));
    // return $response
    //   ->withHeader('Content-Type', 'application/json');
  }
  public function agentsTeams($request, $response, $args)
  {
    if ($args['groupid'] && is_numeric($args['groupid'])) {
      $groups = DB::table('schedule_agentsgroups')->where('id', $args['groupid'])->orWhere('parent', $args['groupid'])->orderBy('parent', 'ASC')->get();
    } else {
      $groups = DB::table('schedule_agentsgroups')->orderBy('parent', 'ASC')->get();
    }
    $group_ids = [];
    foreach ($groups as $group) {
      $group_ids[] = $group->id;
    }
    $agents = DB::table('schedule_agents_to_groups as g')
      ->join('tbladmins as a', 'a.id', '=', 'g.agent_id')
      ->leftJoin('schedule_agents_details as ad', 'ad.agent_id', '=', 'a.id')
      ->leftJoin('schedule_slackusers as su', 'su.agent_id', '=', 'a.id')
      ->whereIn('g.group_id', array_values($group_ids))
      ->orderBy('a.firstname')
      ->orderBy('a.lastname')
      ->get(['a.firstname', 'a.lastname', 'a.id as adminid', 'g.group_id', 'ad.ldap_username', 'ad.ldap_phone',
    'su.slackid', 'su.realname', 'su.realnamenormalized', 'su.phone', 'su.email']);

    //Take sum of valid days-off per agent
    $daysoff = collect(DB::table('schedule_daysoff')->where('date_expiration', '>', date('Y-m-d'))->groupBy('agent_id')->get(['agent_id', DB::raw('SUM(days) as days_sum')]))->keyBy('agent_id');
    //If we have refdate, we can calculate vacations per week per agent
    if ($_GET['refdate']) {
      $datesRange = DatesHelper::getWeekRangeBasedOnDay($_GET['refdate']);
      $vacations = collect(DB::table('schedule_vacations')->whereBetween('day', $datesRange)->groupBy('agent_id')->get(['agent_id', DB::raw('count(*) as days')]))->keyBy('agent_id');
    }



    $groups_out = [];
    foreach ($groups as $group) {
      $groups_out[$group->id]['data'] = $group;
    }

    foreach ($groups_out as $k => $gr) {
      foreach ($agents as $agent) {
        if ($agent->group_id == $k) {

          $agent->daysoff = $daysoff[$agent->adminid] ? (int)$daysoff[$agent->adminid]->days_sum : '-';

          $agent->vacations = $vacations[$agent->adminid] ? (int)$vacations[$agent->adminid]->days : '-';
          $groups_out[$k]['members'][] = $agent;
        }
      }
    }

    $resp = ['agents' => $agents, 'groups_subgroups' => $groups_out];
    return Response::json($resp, $response);
  }
  public function teamsMembers($request, $response, $args)
  {
    $r = DB::table('schedule_agentsgroups as g')
      ->leftJoin('schedule_agents_to_groups as ag', 'ag.group_id', '=', 'g.id')
      ->leftJoin('tbladmins as a', 'a.id', '=', 'ag.agent_id')
      ->leftJoin('schedule_agents_details as ad', 'a.id', '=', 'ad.agent_id')
      
      ->orderBy('g.id')
      ->get(
        ['g.*', 'g.color as groupcolor', 'g.bgcolor as groupbgcolor', 'a.id AS agent_id', 'a.username', 'a.firstname', 'a.lastname', 'ad.color', 'ad.bg']
      );


    $teams = [];
    foreach ($r as $team) {
      if (!isset($teams[$team->id]))
        $teams[$team->id] = ['name' => $team->group, 'members' => [], 'groupid' => $team->id, 'parent' => $team->parent];
      if ($team->username) {
        $teams[$team->id]['members'][] = [
          'agent_id' => $team->agent_id,
          'name' => $team->firstname . ' ' . $team->lastname,
          'username' => $team->username,
          'color' => $team->color ?? null,
          'bg' => $team->bg ?? null,
          // 'daysoff' => $daysoff[$team->agent_id] ? (int)$daysoff[$team->agent_id]->days_sum : '-',
          // 'vacations' => $vacations[$team->agent_id] ? (int)$vacations[$team->agent_id]->days : '-'
        ];
      }
    }
    $sorted = [];
    foreach ($teams as $k => $t) {
      if ($t['parent'] == 0) {
        $sorted[] = $t;
        $subteams = $teams;
        $subteams = array_filter($subteams, function ($v) use ($t) {
          return $v['parent'] === $t['groupid'] ? true : false;
        });
        if ($subteams) {
          array_push($sorted, ...$subteams);
        }
      }
    }
    return Response::json($sorted, $response);
    // $response->getBody()->write(json_encode($teams));
    // return $response
    //   ->withHeader('Content-Type', 'application/json');
  }
  public function addMemberToTeam($request, $response, $args)
  {
    $team =  $request->getParsedBody()['team_id'];
    $agent =  $request->getParsedBody()['agent'];
    DB::table('schedule_agents_to_groups')->updateOrInsert(['agent_id' => $agent], ['group_id' => $team]);

    $resp = ['response' => 'success'];
    return Response::json($resp, $response);
    // $response->getBody()->write(json_encode($resp));
    // return $response
    //   ->withHeader('Content-Type', 'application/json');
  }
  public function setAgentsColor($request, $response, $args)
  {
    $color =  $request->getParsedBody()['value'];
    $agent =  $request->getParsedBody()['admin'];
    $colortype = $request->getParsedBody()['color'];
    DB::table('schedule_agents_details')->updateOrInsert(
      ['agent_id' => $agent],
      [$colortype => $color]
    );
    $resp = ['response' => 'success'];
    return Response::json($resp, $response);
    // $response->getBody()->write(json_encode($resp));
    // return $response
    //   ->withHeader('Content-Type', 'application/json');
  }
  public function myinfo($request, $response, $args)
  {
    if (AgentConstants::adminid()) {
      $admin = (array) DB::table('tbladmins')->where('id', AgentConstants::adminid())->first();
      $resp = ['response' => 'success', 'info' => $admin['firstname'] . ' ' . $admin['lastname']];
    } else {
      $resp = ['response' => 'error', 'msg' => 'Not logged as admin'];
    }
    return Response::json($resp, $response);
    // $response->getBody()->write(json_encode($resp));
    // return $response
    //   ->withHeader('Content-Type', 'application/json');
  }
  public function getAgentInfo($request, $response, $args)
  {
    $agentid = (int)$args['agentid'];
    if ($agentid) {
      $admin = DB::table('tbladmins')->where('id', $agentid)->first(['id', 'firstname', 'lastname', 'username', 'disabled']);
      $resp = ['response' => 'success', 'data' => $admin];
    } else {
      $resp = ['response' => 'error', 'msg' => 'Not logged as admin'];
    }
    return Response::json($resp, $response);
    // $response->getBody()->write(json_encode($resp));
    // return $response
    //   ->withHeader('Content-Type', 'application/json');
  }

  public function verifyAgent($request, $response, $args)
  {
    $author = AgentConstants::adminid();
    if (
      !EditorsAuth::isEditor()
    ) {
      $data['response'] = 'No permission for this operation';
    } else {
      $data['response'] = 'success';
    }
    return Response::json($data, $response);
    // $response->getBody()->write(json_encode($data));
    // return $response
    //   ->withHeader('Content-Type', 'application/json');

  }
}

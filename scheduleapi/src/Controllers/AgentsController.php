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
      ->leftJoin('schedule_editors AS e', 'e.editor_id', '=', 'a.id')
      ->orderBy('a.firstname', 'ASC')
      ->get(['a.id', 'a.firstname', 'a.lastname', 'a.username', 'ad.color', 'ad.bg', 'agg.id AS groupid', 'agg.group', 'e.editor_id AS editor']);
    $row = [];
    foreach ($data as $d) {
      $row[$d->id] = (array)$d;
      $row[$d->id]['fullrow'] = '#' . $d->id . ' ' . $d->firstname . ' ' . $d->lastname . ' (' . $d->username . ')';
      $row[$d->id]['groups'][] = $d->group ? ['name' => $d->group, 'id' => $d->groupid] : [];
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

      $nextOrderNumber =  DB::table('schedule_agentsgroups')->where(['parent' => $parent])->count() + 1;
      $groupid = DB::table('schedule_agentsgroups')->insertGetId(['group' => $name, 'parent' => $parent, 'order' => $nextOrderNumber]);
      $payload = ['response' => 'success'];
    } else {
      $payload = ['response' => 'This name already exists'];
    }

    return Response::json($payload, $response);
  }
  public function agentsTeams($request, $response, $args)
  {
    if ($args['groupid'] && is_numeric($args['groupid'])) {
      $groups = DB::table('schedule_agentsgroups')
        ->where('id', $args['groupid'])
        ->orWhere('parent', $args['groupid'])
        ->orderBy('parent', 'DESC')
        ->orderBy('order', 'ASC')
        ->get();
    } else {
      $groups = DB::table('schedule_agentsgroups')->orderBy('parent', 'DESC')->orderBy('order', 'ASC')->get();
    }
    //
    $group_ids = [];
    foreach ($groups as $group) {
      $group_ids[] = $group->id;
    }
    $agents = DB::table('schedule_agents_to_groups as g')
      ->join('tbladmins as a', 'a.id', '=', 'g.agent_id')
      ->join('schedule_agentsgroups as ag', 'ag.id', '=', 'g.group_id')
      ->leftJoin('schedule_agents_details as ad', 'ad.agent_id', '=', 'a.id')
      ->leftJoin('schedule_slackusers as su', 'su.agent_id', '=', 'a.id')
      ->whereIn('g.group_id', array_values($group_ids))
      ->where('a.disabled', '0')
      ->orderBy('a.firstname')
      ->orderBy('a.lastname')
      ->get([
        'a.firstname', 'a.lastname', 'a.id as adminid', 'g.group_id', 'ad.ldap_username', 'ad.ldap_phone',
        'su.slackid', 'su.realname', 'su.realnamenormalized', 'su.phone', 'su.email', 'ag.order as grouporder'
      ]);

    //Take sum of valid days-off per agent
    $daysoff = collect(DB::table('schedule_daysoff')->where('date_expiration', '>', date('Y-m-d'))->groupBy('agent_id')->get(['agent_id', DB::raw('SUM(days) as days_sum')]))->keyBy('agent_id');
    //If we have refdate, we can calculate vacations per week per agent
    if ($_GET['refdate']) {
      $datesRange = DatesHelper::getWeekRangeBasedOnDay($_GET['refdate']);
      $vacations = collect(DB::table('schedule_vacations')->whereBetween('day', $datesRange)->groupBy('agent_id')->get(['agent_id', DB::raw('count(*) as days')]))->keyBy('agent_id');
    }

    $groups_out = [];
    foreach ($groups as $group) {
      $groups_out[]['data'] = $group;
    }

    foreach ($groups_out as $k => $gr) {
      foreach ($agents as $agent) {
        if ($agent->group_id == $gr['data']->id || $agent->group_id == $gr['data']->parent) {

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
      //->where('a.disabled', '0')
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

        $subteams = $teams;
        $subteams = array_filter($subteams, function ($v) use ($t) {
          return $v['parent'] === $t['groupid'] ? true : false;
        });
        $t['children'] = $subteams ? 1 : 0;
        $sorted[] = $t;
        if ($subteams) {
          // $t['children'] = 1;

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
    if($team === '')
    {
      DB::table('schedule_agents_to_groups')->where('agent_id', $agent)->delete();
    }
    else
    {
      DB::table('schedule_agents_to_groups')->updateOrInsert(['agent_id' => $agent], ['group_id' => $team]);
    }

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
      $resp = ['response' => 'success', 'info' => $admin['firstname'] . ' ' . $admin['lastname'], 'admin_id' => $admin['id']];
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
  public function verifyAdmin($request, $response, $args)
  {
    if (
      !EditorsAuth::isEditor()
    ) {
      $data['response'] = 'No permission for this operation';
    } else {
      $data['response'] = 'success';
    }
    return Response::json($data, $response);
  }

  public function verifyAgent($request, $response, $args)
  {
    $groups = EditorsAuth::getEditorGroups();

      $data['response'] = 'success';

      $data['gr'] = $groups;
    if (EditorsAuth::isAdmin()) {
      $data['admin'] = 1;
    }

    return Response::json($data, $response);
    // $response->getBody()->write(json_encode($data));
    // return $response
    //   ->withHeader('Content-Type', 'application/json');

  }

  public function AgentsFromTickets($request, $response, $args)
  {
    $author = AgentConstants::adminid();

    if (
      $author || 1
    ) {
      $operators = array_map(function ($item) {
        return $item->admin;
      }, DB::table('tblticketreplies')->distinct()->where('admin', '!=', '')
        ->whereBetween('date', [date("Y-m-d H:i:s", strtotime("last year January 1st")), date('Y-m-d H:i:s')])
        ->get(['admin']));
      $data['response'] = 'success';
      $data['operators'] = $operators;
    } else {
      $data['response'] = 'No permission for this operation';
    }
    return Response::json($data, $response);
    // $response->getBody()->write(json_encode($data));
    // return $response
    //   ->withHeader('Content-Type', 'application/json');

  }

  public function AgentsPersonalStatsTickets($request, $response, $args)
  {
    $author = AgentConstants::adminid();

    $agent = urldecode($_GET['agent']);
    $dateFrom = $_GET['dateFrom'];
    $dateTo = $_GET['dateTo'];
    if (!EditorsAuth::isEditor()) {
      $agent = AgentConstants::adminname();
    }
    if (
      $author
    ) {
      $avgFirstReply = DB::select('SELECT AVG(diff) AS avg_firstreply, COUNT(tid) AS replies_count FROM (SELECT time_to_sec(TIMEDIFF(tr.date, t.date)) AS diff, tr.tid
      FROM tbltickets t
      JOIN tblticketreplies tr ON tr.id = (SELECT id FROM tblticketreplies where tid = t.id AND admin != "" ORDER BY id ASC LIMIT 1 )
      WHERE t.date BETWEEN ? AND ? AND tr.admin = ?
      ) AS avg_result', [$dateFrom . '  00:00:00', $dateTo . ' 23:59:59', $agent]);

      $avgReplyGeneral = DB::select('SELECT COUNT(*) AS replies_count, AVG(time_diff) AS avg_seconds FROM (SELECT time_to_sec(TIMEDIFF(trr.date, tr.date)) AS time_diff
      FROM tblticketreplies tr
     	JOIN tblticketreplies trr ON trr.id = (SELECT id FROM tblticketreplies WHERE tid = tr.tid AND id > tr.id && admin != "" ORDER BY id ASC LIMIT 1)
      WHERE tr.date BETWEEN ? AND ? AND tr.admin = ""
      AND (SELECT COUNT(id) FROM tblticketreplies WHERE tid = tr.tid AND id < tr.id && admin != "") > 0
      AND trr.admin = ?) AS avgtime
      ', [$dateFrom . '  00:00:00', $dateTo . ' 23:59:59', $agent]);

      $lastRepliesCount = DB::select('SELECT COUNT(tr.id) as lastrepliescount
      FROM tblticketreplies tr
      WHERE NOT EXISTS (SELECT * FROM tblticketreplies tr2 WHERE tr2.tid = tr.tid AND tr2.id > tr.id)
		  AND tr.date BETWEEN ? AND ? AND tr.admin = ?', [$dateFrom . '  00:00:00', $dateTo . ' 23:59:59', $agent])[0];

      $data['response'] = 'success';
      $data['operators'] = ['avgFirstReply' => $avgFirstReply[0] ?? null, 'avgReply' => $avgReplyGeneral[0] ?? null];

      $data['stats'] = [
        'agent' => $agent,
        'avgfirstreply' => $avgFirstReply[0] ? round($avgFirstReply[0]->avg_firstreply, 2) : null,
        'avgreply' => $avgReplyGeneral[0] ? round($avgReplyGeneral[0]->avg_seconds, 2) : null,
        'tickets' => $avgFirstReply[0] ? $avgFirstReply[0]->replies_count : null,
        'totalreplies' => $avgReplyGeneral[0] ? $avgReplyGeneral[0]->replies_count : null,
        'lastreplies' => $lastRepliesCount->lastrepliescount
      ];
    } else {
      $data['response'] = 'No permission for this operation';
    }
    return Response::json($data, $response);
    // $response->getBody()->write(json_encode($data));
    // return $response
    //   ->withHeader('Content-Type', 'application/json');

  }
}

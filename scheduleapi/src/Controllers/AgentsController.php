<?php

namespace App\Controllers;
use WHMCS\Database\Capsule as DB;
class AgentsController
{
    public function agents($request, $response, $args)
    {
    $data = DB::table("tbladmins as a")->where('a.disabled', '0')
            ->leftJoin('schedule_agents_details AS ad', 'ad.agent_id','=','a.id')
            ->leftJoin('schedule_agents_to_groups AS ag', 'ag.agent_id','=','a.id')
            ->leftJoin('schedule_agentsgroups AS agg', 'ag.group_id','=','agg.id')
            ->orderBy('a.firstname','ASC')
            ->get(['a.id', 'a.firstname', 'a.lastname', 'a.username', 'ad.color', 'ad.bg', 'agg.id AS groupid', 'agg.group']);
            $row = [];
            foreach($data as $d)
            {   
                $row[$d->id] = (array)$d;
               $row[$d->id]['groups'][] = ['name' => $d->group, 'id' => $d->groupid];

            }
    $row=array_values($row);
    $payload = json_encode($row);
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    public function addGroup($request, $response, $args)
    {
    	 $name =  $request->getParsedBody()['name'];
    	 $agents =  $request->getParsedBody()['agents'];
    if(!$groupid = DB::table('schedule_agentsgroups')->where(['group' => $name.'test'])->value('id'))
    {
        $groupid = DB::table('schedule_agentsgroups')->insertGetId(['group' => $name]);
    }
    //$groupid = DB::table('schedule_agentsgroups')->insertGetId(['group' => $name]);
    $rows = [];
    foreach($agents as $agent)
    {	
    	$rows[] = ['group_id' => $groupid, 'agent_id' => $agent];
    }
    if($rows)
    DB::table('schedule_agents_to_groups')->insert($rows);
    $resp = ['response' => 'success'];
    $response->getBody()->write(json_encode($resp)); 
    return $response
          ->withHeader('Content-Type', 'application/json');
    }
    public function teamsMembers($request, $response, $args)
    {
    	$r = DB::table('schedule_agentsgroups as g')
    	->leftJoin('schedule_agents_to_groups as ag', 'ag.group_id', '=', 'g.id')
    	->leftJoin('tbladmins as a', 'a.id', '=', 'ag.agent_id')
    	->leftJoin('schedule_agents_details as ad', 'a.id', '=', 'ad.agent_id')
    	->get(
    		['g.*','a.id AS agent_id', 'a.username', 'a.firstname','a.lastname', 'ad.color', 'ad.bg']);

    	$teams = [];
    	foreach($r as $team)
    	{
    		if(!isset($teams[$team->id]))
    		$teams[$team->id] = ['name' => $team->group, 'members' => [],'groupid' => $team->id];
    		if($team->username)
    		{
    			$teams[$team->id]['members'][] = ['agent_id' => $team->agent_id, 
                'name' => $team->firstname.' '.$team->lastname, 
                'username' =>$team->username, 
                'color' => $team->color ?? null, 
                'bg' => $team->bg ?? null
            ];
    		}
    	}
    	$response->getBody()->write(json_encode($teams)); 
    	return $response
          ->withHeader('Content-Type', 'application/json');
    }
         public function addMemberToTeam($request, $response, $args)
    {
        $team =  $request->getParsedBody()['team_id'];
        $agent =  $request->getParsedBody()['agent'];
        DB::table('schedule_agents_to_groups')->updateOrInsert(['agent_id'=> $agent], ['group_id'=> $team]);
       
            $resp = ['response' => 'success'];

            $response->getBody()->write(json_encode($resp)); 
            return $response
                  ->withHeader('Content-Type', 'application/json');
    }
   public function  setAgentsColor($request, $response, $args)
   {
         $color =  $request->getParsedBody()['value'];
         $agent =  $request->getParsedBody()['admin'];
         $colortype = $request->getParsedBody()['color'];
    DB::table('schedule_agents_details')->updateOrInsert(
    ['agent_id' => $agent], 
    [$colortype => $color]
);
$resp = ['response' => 'success'];
 $response->getBody()->write(json_encode($resp)); 
            return $response
                  ->withHeader('Content-Type', 'application/json');

   }
     public function myinfo($request, $response, $args)
   {
  if($_SESSION['adminid'])
  {
    $admin = (array) DB::table('tbladmins')->where('id', $_SESSION['adminid'])->first(); 
    $resp = ['response' => 'success', 'info' => $admin['firstname'].' '.$admin['lastname']];
  }
  else
  {
    $resp = ['response' => 'error', 'msg' => 'Not logged as admin'];
  }
        $response->getBody()->write(json_encode($resp)); 
            return $response
                  ->withHeader('Content-Type', 'application/json');

   }
}

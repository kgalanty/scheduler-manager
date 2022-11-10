<?php

namespace App\Controllers;

use App\Functions\ShiftsHelper;
use WHMCS\Database\Capsule as DB;
use App\Responses\Response;

class TeamsController
{
    public function get($request, $response, $args)
    {
        try {

            $result = DB::table('schedule_agentsgroups as g')
                ->leftJoin('schedule_agents_to_groups as ag', 'ag.group_id', '=', 'g.id')
                ->leftJoin('tbladmins as a', 'a.id', '=', 'ag.agent_id')
                ->leftJoin('schedule_agents_details as ad', 'a.id', '=', 'ad.agent_id')
                ->orderBy('g.order', 'ASC')
                ->get(
                    ['g.*', 'g.color as groupcolor', 'g.bgcolor as groupbgcolor', 'a.id AS agent_id', 'a.username', 'a.firstname', 'a.lastname', 'ad.color as agent_color', 'ad.bg as agent_bgcolor', 'g.color']
                );

                $shiftsQuery = DB::table('schedule_shifts')->orderBy('from')->get();

        } catch (\Exception $e) {
            return Response::json(['response' => 'error', 'msg' => $e->getMessage()], $response);
        }

        $teams = [];
        foreach ($result as $team) {
            if (!isset($teams[$team->id])) {
                $teams[$team->id] = ['order' => $team->order, 'name' => $team->group, 'members' => [], 'group_id' => $team->id, 'parent' => $team->parent, 'shifts' => [],  'color' => $team->color, 'bgcolor' => $team->bgcolor];
            } 
            if ($team->username) {
                $teams[$team->id]['members'][] = [
                    'agent_id' => $team->agent_id,
                    'name' => $team->firstname . ' ' . $team->lastname,
                    'username' => $team->username,
                    'color' => $team->agent_color ?? null,
                    'bg' => $team->agent_bgcolor ?? null,
              
                ];
            }

            if (count($teams[$team->id]['shifts']) > 0) {
                ShiftsHelper::sortShifts($teams[$team->id]['shifts']);
            }
        }

        foreach ($teams as $k => &$t) {
            if ($t['parent'] == 0) {

                $subteams = $teams;
                $subteams = array_filter($subteams, function ($v) use ($t) {
                    return $v['parent'] === $t['group_id'] ? true : false;
                });

                $t['hasSubteams'] = $subteams ? true : false;
                $t['shifts'] = array_values(array_filter($shiftsQuery, function ($v) use ($t) {
                    return $v->group_id === $t['group_id'] ? true : false;
                }));
            }
        }

        return Response::json(['response' => 'success', 'teams' => array_values($teams)], $response);
    }
}

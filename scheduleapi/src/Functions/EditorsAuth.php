<?php

namespace App\Functions;

use WHMCS\Database\Capsule as DB;
use Illuminate\Support\Collection;
use App\Constants\AgentConstants;

class EditorsAuth
{
	public static function isAdmin()
	{
		return in_array(AgentConstants::adminid(), [98,230]);
	}

	public static function hasPermission(int $perm_id, int $group_id) 
	{
		return (int) DB::table('schedule_agents_groups_editor as e')
			->where('e.group_id', $group_id)
			->where('e.permission', $perm_id)
			->where('e.agent_id', AgentConstants::adminid())
			->count();
	}
	public static function isEditor()
	{
		if (!AgentConstants::adminid()) {
			return false;
		}
		$own_group_query = DB::select(DB::raw('SELECT agg.id AS own_group_id FROM schedule_editors AS e
		JOIN schedule_agents_to_groups AS ag ON ag.agent_id = e.editor_id
		JOIN schedule_agentsgroups AS agg ON agg.id = ag.group_id
		WHERE e.editor_id = ' . AgentConstants::adminid()));
		if ($own_group_query) {
			return $own_group_query[0]->own_group_id;
		}
		return false;
		return DB::table('schedule_editors as e')
			->where('e.editor_id', AgentConstants::adminid())->count() == 0 ? false : true;
		// return DB::table('schedule_agents_groups_editor as e')
		// 	->where('e.agent_id', AgentConstants::adminid())
		// 	->where('e.group_id', $group_id)->count() == 0 ? false : true;
	}

	public static function getEditorGroups()
	{
		$groups = DB::table('schedule_agents_groups_editor as e')
			->where('agent_id', AgentConstants::adminid())
			->get(['group_id', 'permission']);

		$groupReturn = [];

		foreach ($groups as $group) {
			$groupReturn[$group->permission][] = $group->group_id;
		}

		return $groupReturn;
	}

	public static function IsAgentInPermittedGroups(int $agent_id, array $permitted_groups_id)
	{
		
	}
}

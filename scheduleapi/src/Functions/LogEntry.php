<?php

namespace App\Functions;

use WHMCS\Database\Capsule as DB;
use Illuminate\Support\Collection;
use App\Constants\AgentConstants;
class LogEntry
{
	public $admins, $shifts, $groups;
	public function __construct()
	{
		$this->admins = collect(DB::table('tbladmins')->get(['id', 'firstname', 'lastname', 'username']))->keyBy('id');
		$this->shifts = collect(DB::table('schedule_shifts')->get())->keyBy('id');
		$this->groups = collect(DB::table('schedule_agentsgroups')->get())->keyBy('id');
	}
	public function createTplConfirmLog($tplid, $dates, $overwrite)
	{
		$tpl_details = DB::table('schedule_templates as t')->join('schedule_agentsgroups as g', 'g.id','=','t.group_id')->
		first(['g.group', 't.name']);
		$overwritten = $overwrite ? ' with overwriting ' : '';
		$log_entry = 'Inserting template '.$tplid.' '.$tpl_details->name.''.$overwritten.'('.$tpl_details->group.') for '.$dates[0].'-'.$dates[1];
		$log = ['author' => AgentConstants::adminid(), 'log' => $log_entry, 'action' => 'Added', 'date' => DB::raw('NOW()')];
		$this->log($log);
		//var_dump(func_get_args());die;
	}
	public  function createAddLogs($entries)
	{
		$this->createLogs($entries, 'Added');
	}
	public  function createLogs($entries, $action)
	{
		$author = AgentConstants::adminid();
		$log = [];
		foreach ($entries as $entry) {
			if(!is_object($entry))
			{
				$entry = (object)$entry;
			}
			$log_entry = $this->groups[$entry->group_id]->group .': '. $this->admins[$entry->agent_id]->firstname . ' ' . $this->admins[$entry->agent_id]->lastname . ', Shift: ' . $this->shifts[$entry->shift_id]->from . '-' .
			$this->shifts[$entry->shift_id]->to;
			$log[] = ['author' => $author, 'log' => $log_entry, 'action' => $action, 'date' => DB::raw('NOW()')];
		}
		$this->log($log);

	}
	public function createDelLogs($entries)
	{
		$this->createLogs($entries, 'Deleted');
	}
	private function log($logs)
	{
		if($logs)
		{
			DB::table('schedule_eventslog')->insert($logs);
		}
	}
}

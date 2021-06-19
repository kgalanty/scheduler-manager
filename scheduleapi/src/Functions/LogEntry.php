<?php

namespace App\Functions;

use WHMCS\Database\Capsule as DB;
use Illuminate\Support\Collection;
use App\Constants\AgentConstants;
use App\Functions\DatesHelper;
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
	public function createAddLogs($entries)
	{
		return $this->createEntryLogs($entries, 'Added');
	}
	public function createEntryLogs($entries, $action)
	{
		$author = AgentConstants::adminid();
		$log = [];
		foreach ($entries as $entry) {
			if(!is_object($entry))
			{
				$entry = (object)$entry;
			}
			$shift = $this->HandleOnCallShift($this->shifts[$entry->shift_id]->from . '-' .$this->shifts[$entry->shift_id]->to);

			$log_entry = $this->groups[$entry->group_id]->group .': '. $this->admins[$entry->agent_id]->firstname . ' ' . $this->admins[$entry->agent_id]->lastname . ' on '.$entry->day.', Shift: ' . $shift;
			$log[] = [
				'author' => $author, 
				'log' => $log_entry, 
				'event_date' => $entry->day,
				'action' => $action, 
				'path' => '/schedule/'.$this->groups[$entry->group_id]->group.'/'.DatesHelper::CreateDateToPathFromOneDate(),
				'date' => DB::raw('NOW()')
			];
		}
		return $log;
		//$this->log($log);

	}
	public function createDelLogs($entries)
	{
		return $this->createEntryLogs($entries, 'Deleted');
	}
	private function log($logs)
	{
		if($logs)
		{
			DB::table('schedule_eventslog')->insert($logs);
		}
	}
	private function HandleOnCallShift(string $shiftlog) : string
	{
		if($shiftlog == 'on-call') return 'On Call';
		return $shiftlog;
	}
}

<?php

namespace App\Functions\Logs;

use App\Functions\Interfaces\ILogs;
use App\Constants\AgentConstants;
use App\Functions\DatesHelper;
use WHMCS\Database\Capsule as DB;
class AddEntryLog implements ILogs
{
    public $admins, $shifts, $groups, $action, $entries;
	public function __construct($entries)
	{
        $this->action = 'Added';
        $this->entries = $entries;
		$this->admins = collect(DB::table('tbladmins')->get(['id', 'firstname', 'lastname', 'username']))->keyBy('id');
		$this->shifts = collect(DB::table('schedule_shifts')->get())->keyBy('id');
		$this->groups = collect(DB::table('schedule_agentsgroups')->get())->keyBy('id');
	}
    public function getLog()
	{
        $author = AgentConstants::adminid();
		$log = [];
		foreach ($this->entries as $entry) {
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
				'action' => $this->action, 
				'path' => '/schedule/'.$this->groups[$entry->group_id]->group.'/'.DatesHelper::CreateDateToPathFromOneDate(),
				'date' => DB::raw('NOW()')
			];
		}
		return $log;
	}
    private function HandleOnCallShift(string $shiftlog) : string
	{
		if($shiftlog == 'on-call') return 'On Call';
		return $shiftlog;
	}

}

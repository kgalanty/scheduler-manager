<?php

namespace App\Functions;

use WHMCS\Database\Capsule as DB;
use Illuminate\Support\Collection;

class LogEntry
{
	public $admins, $shifts, $groups;
	public function __construct()
	{
		$this->admins = collect(DB::table('tbladmins')->get(['id', 'firstname', 'lastname', 'username']))->keyBy('id');
		$this->shifts = collect(DB::table('schedule_shifts')->get())->keyBy('id');
		$this->groups = collect(DB::table('schedule_agentsgroups')->get())->keyBy('id');
	}
	public  function createAddLogs($entries)
	{
		$this->createLogs($entries, 'Added');
	}
	public  function createLogs($entries, $action)
	{
		$author = $_SESSION['adminid'];
		$log = [];
		foreach ($entries as $entry) {
			$log_entry = $this->groups[$entry->group_id]->group .': '. $this->admins[$entry->agent_id]->firstname . ' ' . $this->admins[$entry->agent_id]->lastname . ', Shift: ' . $this->shifts[$entry->shift_id]->from . '-' .
			$this->shifts[$entry->shift_id]->to;
			$log[] = ['author' => $author, 'log' => $log_entry, 'action' => $action, 'date' => DB::raw('NOW()')];
		}
		DB::table('schedule_eventslog')->insert($log);

	}
	public function createDelLogs($entries)
	{
		$this->createLogs($entries, 'Deleted');
	}
}

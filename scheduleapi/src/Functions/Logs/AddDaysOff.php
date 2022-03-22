<?php

namespace App\Functions\Logs;

use App\Functions\Interfaces\ILogs;
use App\Constants\AgentConstants;
use App\Functions\DatesHelper;
use WHMCS\Database\Capsule as DB;

class AddDaysOff implements ILogs
{
	public $action, $params;
	/**
	 * $params: agent_id, daysoff, year
	 */
	public function __construct(array $params)
	{
		$this->action = 'Added DaysOff';
		$this->params = $params;
	}
	public function getLog()
	{
		$authorid = AgentConstants::adminid();

		$author = DB::table('tbladmins')->where('id', $authorid)->first(['firstname', 'lastname']);
		$agent =  DB::table('tbladmins')->where('id', $this->params['agent_id'])->first(['firstname', 'lastname']);
		$log = [
			'author' => $authorid,
			'log' => $author->firstname . ' ' . $author->lastname . ' added ' . $this->params['daysoff'] . ' days to ' . $agent->firstname . ' ' . $agent->lastname . ', year ' . $this->params['year'],
			'event_date' => date('Y-m-d'),
			'action' => 'Added DaysOff',
			'path' => 'daysoff/' . $this->params['agent_id'],
			'date' => DB::raw('NOW()')
		];

		return $log;
	}
}

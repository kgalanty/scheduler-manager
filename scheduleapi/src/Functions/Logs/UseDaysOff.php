<?php

namespace App\Functions\Logs;

use App\Functions\Interfaces\ILogs;
use App\Constants\AgentConstants;
use App\Functions\DatesHelper;
use WHMCS\Database\Capsule as DB;

class UseDaysOff implements ILogs
{
	public $action, $params;
	/**
	 * $params: agent_id, path, dayoff
	 */
	public function __construct(array $params)
	{
		$this->action = 'Use DaysOff';
		$this->params = $params;
	}
	public function getLog()
	{
		$authorid = AgentConstants::adminid();

		$author = DB::table('tbladmins')->where('id', $authorid)->first(['firstname', 'lastname']);
		$agent =  DB::table('tbladmins')->where('id', $this->params['agent_id'])->first(['firstname', 'lastname']);
		$log = [
			'author' => $authorid,
			'log' => $author->firstname . ' ' . $author->lastname . ' added vacation to ' . $this->params['dayoff'] . ' for ' . $agent->firstname . ' ' . $agent->lastname . '',
			'event_date' => date('Y-m-d'),
			'action' => $this->action,
			'path' => $this->params['path'],
			'date' => DB::raw('NOW()')
		];

		return $log;
	}
}

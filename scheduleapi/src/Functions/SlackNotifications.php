<?php

namespace App\Functions;

use WHMCS\Database\Capsule as DB;
use Illuminate\Support\Collection;
use App\Constants\AgentConstants;
use App\Constants\SlackConstants;
use App\Functions\DatesHelper;
use App\Functions\Interfaces\INotify;

class SlackNotifications
{
	// //public $admins, $shifts, $groups;
	public $api, $notification;
	// public $message, $slackid;
	public function __construct(INotify $notification)
	{
		$this->notification = $notification;
		$this->api = \JoliCode\Slack\ClientFactory::create(SlackConstants::TOKEN);
	}
	public function send()
	{
		$this->notification->send($this->api);
	}
	// public function sendWeek()
	// {
	// 	$entriesPerAgent = [];
	// 	foreach ($this->entries as $entry) {
	// 		$entriesPerAgent[$entry->agent_id][] = $entry->day . ' (' . $entry->from . '-' . $entry->to . ') ' . $entry->group;
	// 	}
	// 	$entriesPerAgentReady = [];
	// 	foreach ($entriesPerAgent as $key => $entriesPerAgentItem) {
	// 		$entriesPerAgentReady[$key] = implode(', ', $entriesPerAgentItem);
	// 	}
	
	// 	foreach ($this->slackUsers as $user) {
	
	// 		$addedShiftsString = $entriesPerAgentReady[$user->agent_id] ? 'Shifts: '.$entriesPerAgentReady[$user->agent_id].' ' : '';
	// 		//channel $user->slackid
	// 		$this->api->chatPostMessage([
	// 			'username' => 'Schedule Bot',
	// 			'channel' => 'UESJKKE84',
	// 			'text' => 'Schedule Manager  for '.$user->realname.': ' . $addedShiftsString,

	// 		]);
			
	// 	}
	// 	echo('<pre>');var_dump($this->entries, $this->slackUsers);die;
	// }
	// public  function send()
	// {
	// 	$entriesPerAgent = [];
	// 	foreach ($this->entries as $entry) {
	// 		$entriesPerAgent[$entry->agent_id][] = $entry->day . ' (' . $entry->from . '-' . $entry->to . ') ' . $entry->group;
	// 	}
	// 	$entriesPerAgentReady = [];
	// 	foreach ($entriesPerAgent as $key => $entriesPerAgentItem) {
	// 		$entriesPerAgentReady[$key] = implode(', ', $entriesPerAgentItem);
	// 	}

	// 	//deleteentries
	// 	foreach ($this->deleteentries as $entry) {
	// 		$delentriesPerAgent[$entry->agent_id][] = $entry->day . ' (' . $entry->from . '-' . $entry->to . ') ' . $entry->group;
	// 	}
	// 	$delentriesPerAgentReady = [];
	// 	foreach ($delentriesPerAgent as $key => $entriesPerAgentItem) {
	// 		$delentriesPerAgentReady[$key] = implode(', ', $entriesPerAgentItem);
	// 	}
	
	// 	foreach ($this->slackUsers as $user) {
	
	// 		$deletedentriesString = $delentriesPerAgentReady[$user->agent_id] ? 'Removed shifts: '.$delentriesPerAgentReady[$user->agent_id] : '';
	// 		$addedShiftsString = $entriesPerAgentReady[$user->agent_id] ? 'Added shifts: '.$entriesPerAgentReady[$user->agent_id].' ' : '';
	// 		//channel $user->slackid
	// 		$this->api->chatPostMessage([
	// 			'username' => 'Schedule Bot',
	// 			'channel' => 'UESJKKE84',
	// 			'text' => 'Schedule Manager update for '.$user->realname.': ' . $addedShiftsString.$deletedentriesString,

	// 		]);
			
	// 	}

	// }
}

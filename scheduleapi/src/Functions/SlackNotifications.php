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
}

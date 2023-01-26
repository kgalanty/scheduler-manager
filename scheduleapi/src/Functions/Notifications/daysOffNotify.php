<?php

namespace App\Functions\Notifications;

use WHMCS\Database\Capsule as DB;
use App\Functions\AgentsHelper;
use App\Functions\DaysOffType;
use App\Functions\Interfaces\INotify;
use App\Functions\ReviewStatus;

class daysOffNotify implements INotify
{
    public $params;
    const NOTIFY_ADMIN_ID = [230, 98];

    public function __construct(array $params)
    {
        //'author_id' => $author, 'datestart' => $datestart, 'dateend' => $dateend, 'mode'
        $this->params = $params;
        //tbladmins entries
        // ['entries' => $entries, 'deleteentries' => $deleteentries]
    }

    public function fetchAgents()
    {
        $agentsSlack = DB::table('schedule_slackusers as su')
            ->whereIn('su.agent_id', self::NOTIFY_ADMIN_ID)
            ->get(['agent_id', 'slackid', 'realname', 'realnamenormalized']);
        //98
        return $agentsSlack;
    }

    public function send($api)
    {
        $agents = $this->fetchAgents();
        $submitter = DB::table('tbladmins as a')->where('id', $this->params['author_id'])->first();
        $daysOffType = (new DaysOffType($this->params['mode']))->giveType();
       
        foreach ($agents as $agent) {
            $api->chatPostMessage([
                'username' => 'Schedule Bot',
                'channel' => $agent->slackid,
                'text' => 'New request for ' . $daysOffType . ' has been submitted by ' . $submitter->firstname . ' ' . $submitter->lastname . ".\nDate range: " . $this->params['datestart'] . ' to ' . $this->params['dateend']

            ]);
        }
        // echo('<pre>'); var_dump($agentsSlack); die;
    }
}

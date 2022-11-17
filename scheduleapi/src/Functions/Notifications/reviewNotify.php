<?php

namespace App\Functions\Notifications;

use WHMCS\Database\Capsule as DB;
use App\Functions\AgentsHelper;
use App\Functions\DaysOffType;
use App\Functions\Interfaces\INotify;
use App\Functions\ReviewStatus;

class reviewNotify implements INotify
{
    public $entries;
    public $admins;

    public function __construct(array $entries)
    {
        $this->entries = $entries['entries'];
        $this->admins = $entries['admins']; //tbladmins entries
        // ['entries' => $entries, 'deleteentries' => $deleteentries]
    }

    public function fetchAgents()
    {
        $agentsSlack = DB::table('schedule_slackusers as su')
            ->where('su.agent_id', $this->entries->getRow()->agent_id)
            ->first(['agent_id', 'slackid', 'realname', 'realnamenormalized']);
        return $agentsSlack;
    }

    public function send($api)
    {
        $agent = $this->fetchAgents();
       
        foreach($this->admins as $admin)
        {
            if($admin->id == $this->entries->agent_id)
            {
                $submitter = $admin;
            }
            if($admin->id == $this->entries->approve_admin_id)
            {
                $reviewAuthor = $admin;
            }
        }
      
        $status = (new ReviewStatus($this->entries->getRow()->approve_status))->giveStatus();
        $daysOffType = (new DaysOffType($this->entries->getRow()->request_type))->giveType();

        $t = $api->chatPostMessage([
            'username' => 'Schedule Bot',
            'channel' => $agent->slackid,
            'text' => 'Your request for '.$daysOffType.' has been '.$status.' by ' . $reviewAuthor->firstname.' '.$reviewAuthor->lastname.'.'.($this->entries->getRow()->approve_response ? ' Additional comment: '.$this->entries->getRow()->approve_response : '')

        ]);

        // echo('<pre>'); var_dump($agentsSlack); die;
    }
}

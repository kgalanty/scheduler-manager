<?php

namespace App\Functions\Notifications;

use WHMCS\Database\Capsule as DB;
use App\Functions\AgentsHelper;
use App\Functions\Interfaces\INotify;

class commitNotify implements INotify
{
    public $entries;
    public function __construct(array $entries)
    {
        $this->entries = $entries;
        // ['entries' => $entries, 'deleteentries' => $deleteentries]
    }
    public function fetchAgents()
    {
        $agents_id = AgentsHelper::getUniqueAgentsIDsFromEntries(array_merge($this->entries['entries'], $this->entries['deleteentries']));
        $agentsSlack = DB::table('schedule_slackusers as su')->whereIn('su.agent_id', $agents_id)->get(['agent_id', 'slackid', 'realname', 'realnamenormalized']);
        return $agentsSlack;
    }
    public function send($api)
    {
        $agentsSlack = \collect($this->fetchAgents())->keyBy('agent_id');

        $entriesPerAgent = [];
        foreach ($this->entries['entries'] as $entry) {
            $entriesPerAgent[$entry->agent_id][] = $entry->group . ' ' . $entry->day . ' (' . $entry->from . '-' . $entry->to . ') ';
        }
        $entriesPerAgentReady = [];
        foreach ($entriesPerAgent as $key => $entriesPerAgentItem) {
            $entriesPerAgentReady[$key] = implode(', ', $entriesPerAgentItem);
        }

        //deleteentries
        foreach ($this->entries['deleteentries'] as $entry) {
            $delentriesPerAgent[$entry->agent_id][] = $entry->group . ' ' . $entry->day . ' (' . $entry->from . '-' . $entry->to . ') ';
        }
        $delentriesPerAgentReady = [];
        foreach ($delentriesPerAgent as $key => $entriesPerAgentItem) {
            $delentriesPerAgentReady[$key] = implode(', ', $entriesPerAgentItem);
        }

        foreach ($agentsSlack as $agent_id => $user) {

            $deletedentriesString = $delentriesPerAgentReady[$agent_id] ? 'Removed shifts: ' . $delentriesPerAgentReady[$agent_id] : '';
            $addedShiftsString = $entriesPerAgentReady[$agent_id] ? 'Added shifts: ' . $entriesPerAgentReady[$agent_id] . "\n" : '';
            //$slack_id = $user->slackid;
            //echo('<pre>'); var_dump($user); die;
            //channel $user->slackid
            $api->chatPostMessage([
                'username' => 'Schedule Bot',
                'channel' => 'U04SEHXUK',
                'text' => 'Schedule Manager update for ' . $user->realname . ":\n" . $addedShiftsString . $deletedentriesString,

            ]);
        }
        // echo('<pre>'); var_dump($agentsSlack); die;
    }
}

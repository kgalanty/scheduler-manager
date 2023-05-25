<?php

namespace App\Functions\Notifications;

use WHMCS\Database\Capsule as DB;
use App\Functions\AgentsHelper;
use App\Functions\Interfaces\INotify;

class weekNotify implements INotify
{
    public $groupid, $dates, $entries;
    public function __construct(int $groupid, array $dates)
    {
        $this->groupid = $groupid;
        $this->dates = $dates;
    }
    public function fetchAgents()
    {
        $this->entries = DB::table('schedule_timetable as t')
            ->join('schedule_slackusers as su', 'su.agent_id', '=', 't.agent_id')
            ->join('schedule_shifts as s', 's.id', '=', 't.shift_id')
            ->join('schedule_agentsgroups as ag', 'ag.id', '=', 't.group_id')
            ->whereBetween('t.day', $this->dates)
            ->where('t.draft', '0')
            ->where('t.group_id', $this->groupid)
            ->orderBy('t.day', 'ASC')
            ->get(['su.*', 's.from', 's.to', 'ag.group', 't.day']);
        $agents_id = AgentsHelper::getUniqueAgentsIDsFromEntries($this->entries);
        return $agents_id;
    }
    public function send($api)
    {
        $agents = $this->fetchAgents();

        $entriesPerAgent = [];
        foreach ($this->entries as $entry) {
            $entriesPerAgent[$entry->agent_id]['entries'][] = $entry->group . ': ' . $entry->day . ' (' . $entry->from . '-' . $entry->to . ')';
            $entriesPerAgent[$entry->agent_id]['details'] = ['agent_id' => $entry->agent_id, 'slackid' => $entry->slackid, 'group' => $entry->group, 'realname' => $entry->realname];
        }
        foreach ($entriesPerAgent as $key => $unused) {
            $entriesPerAgent[$key]['entries'] = implode("\n- ", $entriesPerAgent[$key]['entries']);
        }
        
 logActivity('Schedule Slack log start: '.print_r($agents, true));
        foreach ($agents as $user) {
            if(!$entriesPerAgent[$user]['details']['slackid']) continue;

            $addedShiftsString = $entriesPerAgent[$user] ? 'Shifts: ' . $entriesPerAgent[$user]['entries'] . '' : '';

            $t=$api->chatPostMessage([
                'username' => 'Schedule Bot',
                'channel' => $entriesPerAgent[$user]['details']['slackid'],
                'text' => 'Schedule Manager Shifts between ' . implode(' and ', $this->dates) . ' for ' . $entriesPerAgent[$user]['details']['realname'] . ":\n" . $addedShiftsString,

            ]);


            logActivity('Schedule Slack message to: '.print_r($entriesPerAgent[$user], true));
            logActivity('Schedule Slack message result: '.print_r($t, true));
        }
    }
}

<?php

namespace App\Functions;

use WHMCS\Database\Capsule as DB;
use Illuminate\Support\Collection;
use App\Constants\AgentConstants;

class AgentsHelper
{
    public static function getUniqueAgentsIDsFromEntries(array $entries)
    {
        $agents_id = [];
        foreach ($entries as $entry) {
            if (!in_array($entry->agent_id, $agents_id)) {
                array_push($agents_id, $entry->agent_id);
            }
        }
        return $agents_id;
    }

    public static function getDaysOffCountByAgent(int $agent_id)
    {
        $daysoff = DB::table('schedule_daysoff')->where('date_expiration', '>', date('Y-m-d'))->groupBy('agent_id')->having('agent_id', '=', $agent_id)->first([DB::raw('SUM(days) as days_sum')]);
        return (int)$daysoff->days_sum ?? 0;
    }
}

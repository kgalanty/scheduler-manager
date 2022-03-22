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
}

<?php

namespace App\Functions;

use WHMCS\Database\Capsule as DB;
use Illuminate\Support\Collection;
use App\Constants\AgentConstants;

class DaysOffHelper
{
    /**
     * array $dates Y-m-d dates for vacation
     * int $agent_id 
     * 
     */
    public static function AddDaysOffVacations(array $dates, $agent_id)
    {
        $rows = [];
        foreach ($dates as $date) {
            $rows[] = ['agent_id' => $agent_id, 'group_id' => '', 'day' => $date, 'draft' => 0, 'author' => AgentConstants::adminid()];
        }

        DB::table('schedule_vacations')->insert($rows);

        return;
    }
    // take days from pool
    public static function SubtractDaysFromHolidays(int $daysCount, int $agent_id)
    {
        $daysPool = DB::table('schedule_daysoff')->where('date_expiration', '>', date('Y-m-d'))->where('agent_id', $agent_id)->orderBy('date_expiration', 'ASC')->first();
        if ($daysPool) {
            DB::table('schedule_daysoff')->where('id', $daysPool->id)->update(['days' => $daysPool->days - $daysCount]);
            return true;
        }
        return false;
    }
    //return days to pool
    public static function AddDaysFromHolidays(int $daysCount, int $agent_id)
    {
        $daysPool = DB::table('schedule_daysoff')->where('date_expiration', '>', date('Y-m-d'))->where('agent_id', $agent_id)->orderBy('date_expiration', 'ASC')->first();
        if ($daysPool) {
            DB::table('schedule_daysoff')->where('id', $daysPool->id)->update(['days' => $daysPool->days + $daysCount]);
            return true;
        }
        return false;
    }
}

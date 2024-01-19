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

    public static function checkIfDaysAreAvailable(int $daysCount, int $agent_id)
    {
        $daysPools = DB::table('schedule_daysoff')->where('date_expiration', '>', date('Y-m-d'))->where('agent_id', $agent_id)->where('days', '>', '0')->orderBy('date_expiration', 'ASC')->get();
        $daysAvailable = 0;
        foreach ($daysPools as $pool) {
            $daysAvailable += $pool->days;
        }
        return $daysAvailable >= $daysCount ? true : false;
    }

    public static function SubtractDaysFromHolidays(int $daysCount, int $agent_id)
    {
        $daysPools = DB::table('schedule_daysoff')->where('date_expiration', '>', date('Y-m-d'))->where('agent_id', $agent_id)->where('days', '>', '0')->orderBy('date_expiration', 'ASC')->get();

        $daysCountcalculation = $daysCount;
        $daysAvailable = 0;

        foreach ($daysPools as $pool) {
            $daysAvailable += $pool->days;
        }

        if ($daysAvailable < $daysCountcalculation) {
            return false;
        }

        foreach ($daysPools as $pool) {
            $thisPoolDays = $pool->days >= $daysCountcalculation ? $pool->days - $daysCountcalculation : 0;

            $daysCountcalculation -= $pool->days;
            DB::table('schedule_daysoff')->where('id', $pool->id)->update(['days' => $thisPoolDays]);
        }

        return true;
    }
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

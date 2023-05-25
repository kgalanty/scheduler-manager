<?php

namespace App\Functions;

use App\Functions\DatesHelper;

class ShiftsHelper
{
    /*
    *  Callable to usort
    */
    public static function sortShifts(&$shifts)
    {
        usort($shifts, function ($a, $b) {
            if (strpos($a['from'], '00:') === 0 && strpos($b['from'], '00:') === false) {
                return 1;
            } elseif (strpos($a['from'], '00:') === false && strpos($b['from'], '00:') === 0) {
                return -1;
            } else {
                return (int)$a['from'] - (int)$b['from'];
            }
        });
    }

    /*
    *   This function takes rows and using $datestart and $dateend it divides rows per $shifts by $dateProperty as property of single element of $rows
    * and returns array of shifts with array of days everyday with counters of occurrence in $rows
    */
    public static function divideToShifts(array $rows, array $shifts, string $datestart, string $dateend, string $dateProperty = 'date')
    {
        $shifts_counters = [];
        $datesBetween = DatesHelper::generateBetweenDates($datestart, $dateend);
        foreach ($datesBetween as $day) {
            foreach ($shifts as $shift) {
                $shiftstart = DatesHelper::convertDateTimeBetweenTimezones($day . ' ' . $shift->from . ':00');
                $shiftend = DatesHelper::convertDateTimeBetweenTimezones($shift->from > $shift->to ? date('Y-m-d', strtotime($day . ' +1 day')) : $day . ' ' . $shift->to . ':00');

                $shifts_counters[$shift->id][ $day ] = ['day' => $day, 'counter' => count(array_filter($rows, function ($r) use ($shiftstart, $shiftend, $dateProperty) {
                    $dateToStr = strtotime($r->$dateProperty);
                    return strtotime($shiftstart) <= $dateToStr && strtotime($shiftend) >= $dateToStr;
                })) ] ;
            }
        }
        return $shifts_counters;
    }
}

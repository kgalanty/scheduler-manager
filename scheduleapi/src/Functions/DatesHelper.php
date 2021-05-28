<?php

namespace App\Functions;

use WHMCS\Database\Capsule as DB;
use Illuminate\Support\Collection;
use App\Constants\AgentConstants;
class DatesHelper
{
    /**
     * int $weekday number from 1-7 range, 1 for monday, 7 for sunday
     * string $startdate Date pointing to monday 
     * 
     */
	public static function getDateBasedOnWeekday(int $weekday, string $startdate)
	{
        if(date('N', strtotime($startdate))!= 1)
        {
            return false;
        }
       return date('Y-m-d', strtotime($startdate.' +'.($weekday-1).' days'));
	}
    /**
    * Validates if given date is correct (starts from monday) and has format Jun07-Jun13-2021
    * If correct, converts date to Y-m-d format
    */
    public static function DatesFromRoute(string $dates)
    {
        $startdateparams = explode('-', $dates, 3);
        $startdateprocessed = date('Y-m-d', strtotime($startdateparams[0] . ' ' . $startdateparams[2]));
        \DateTime::createFromFormat('Md Y', $startdateparams[0]. ' ' . $startdateparams[2]);
        $date_errors = \DateTime::getLastErrors();
        if($date_errors['warning_count'] + $date_errors['error_count'] > 0 || date('D', strtotime($startdateparams[0] . ' ' . $startdateparams[2])) != 'Mon')
        {
            return false;
        }
        $enddate = date('Y-m-d', strtotime($startdateprocessed . ' +6 days'));
        return [$startdateprocessed, $enddate];
    }

}

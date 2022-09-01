<?php

namespace App\Functions;

use WHMCS\Database\Capsule as DB;
use Illuminate\Support\Collection;
use App\Constants\AgentConstants;
class DatesHelper
{
    /**
     * string $startdate Date pointing to monday 
     * returns 2 elements array with Y-m-d monday date and sunday date
     */
    public static function getWeekRangeBasedOnDay(string $startdate) : array
	{
        $monday = date('Y-m-d', strtotime($startdate.' -'.(date('N', strtotime($startdate))-1).' days'));
        $sunday = date('Y-m-d', strtotime($startdate.' +'.(7-date('N', strtotime($startdate))).' days'));
       return [$monday, $sunday];
	}
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
    public static function CreateDateToPathFromOneDate(string $date = null)
    {
        if(!$date) $date = date('Y-m-d');
        $strtotime = strtotime($date);
        $weekday = (int)date('N', $strtotime)-1; //integer for cweekday
        $startdate = date('Md', strtotime($date.' -'.$weekday.' days'));
        $enddate = date('Md', strtotime($date.' +'.(6-$weekday).' days'));
        $year = date('Y', $strtotime);
        return $startdate.'-'.$enddate.'-'.$year;
    }
    public static function generateBetweenDates($start, $end, $format = 'Y-m-d' )
    {
        $array = array();
        $interval = new \DateInterval('P1D');

        $realEnd = new \DateTime($end);
        $realEnd->add($interval);

        $period = new \DatePeriod(new \DateTime($start), $interval, $realEnd);

        foreach ($period as $date) {
            $array[] = $date->format($format);
        }

        return $array;
    }

    public static function convertDateTimeBetweenTimezones($datetime, $tzFrom = 'Europe/Sofia', $tzTo = 'America/Chicago')
    {
        return (new \DateTime($datetime, new \DateTimeZone($tzFrom)))
            ->setTimezone(new \DateTimeZone($tzTo))
            ->format('Y-m-d H:i:s');
    }
}

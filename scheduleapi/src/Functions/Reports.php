<?php

namespace App\Functions;

use WHMCS\Database\Capsule as DB;
use App\Functions\DatesHelper;
class Reports
{
    public $config, $sqlresults, $processedOutput;
    public function __construct(array $config)
    {
        $this->config = $config;
    }
    public function retrieveReport()
    {
        $this->sqlresults = DB::table('schedule_timetable as t')
            ->join('schedule_shifts as s', 's.id', '=', 't.shift_id')
            ->where(
                ['t.agent_id' => $this->config['worker_id'], 't.draft' => 0]
            )->whereBetween('t.day', [$this->config['startDate'], $this->config['endDate']])
            ->get(['t.*', 's.from', 's.to']);
        return $this;
    }
    public function segregateByDays()
    {
        $output = [];
        if ($this->sqlresults) {
            foreach ($this->sqlresults as $result) {
                //$fromTo = $result->from . ' ' . $result->to;
                $output[$result->day][] = $result->from.' '.$result->to;
            }
        }
        $this->processedOutput = $output;
        
        return $this;
    }
    public function prepareRowsCellsData()
    {
        $days = DatesHelper::generateBetweenDates($this->config['startDate'], $this->config['endDate']);
        $rows = [];
        $ShiftsPerDay = [];
        for ($i = 0; $i < count($days); $i++) {
            //$ShiftsPerDay[$i] = [$days[$i]];
            $ShiftsPerDay[$i] = [];
            $ShiftsPerDay[$i] = array_merge($ShiftsPerDay[$i], $this->processedOutput[$days[$i]] );
            if(!$ShiftsPerDay[$i])
            {
                $ShiftsPerDay[$i] = [];
            }
            // foreach($this->sqlresults as $result)
            // {
            //     if($result->day == $days[$i])
            //     {
            //         $ShiftsPerDay[$i]++;
            //     }
            // }
        }
        $r = array_map(null, ...$ShiftsPerDay);
       // echo('<pre>');  var_dump( $r, $ShiftsPerDay ); die;
        // for ($i = 0; $i < count($days); $i++) {
        //     foreach($this->sqlresults as $result)
        //     {
        //         if($result->day == $days[$i])
        //         {
        //             array_push($rows, $result->from.' '.$result->to);
        //         }
        //     }
        // }
        // echo ('<pre>');
        // //var_dump(, $this->sqlresults, $this->config['startDate']);
        // die;
        return $r;
    }
   
}

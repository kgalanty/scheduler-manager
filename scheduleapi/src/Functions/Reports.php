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
        $admins_id = [];
        foreach ($this->config['workers_id'] as $w) {
            $admins_id[] = $w->agent_id;
        }

        $this->sqlresults = DB::table('schedule_timetable as t')
            ->join('schedule_shifts as s', 's.id', '=', 't.shift_id')
            ->join('tbladmins as ad', 'ad.id', '=', 't.agent_id')
            ->whereIn('t.agent_id', $admins_id)
            ->where('t.draft', 0)
            ->whereBetween('t.day', [$this->config['startDate'], $this->config['endDate']])
            ->get(['t.*', 's.from', 's.to', 'ad.firstname', 'ad.lastname']);
          
        return $this;
    }
    public function segregateByShiftsDays()
    {
        $output = [];
        if ($this->sqlresults) {
            foreach ($this->sqlresults as $r) {
                $output[$r->shift_id][$r->day][] = $r;
            }
        }
       $this->processedOutput = $output;
        return $this;
    }
    public function segregateByDays()
    {
        $output = [];

        if ($this->sqlresults) {
            foreach ($this->sqlresults as $result) {
                //$fromTo = $result->from . ' ' . $result->to;
                $output[$result->day][] = $result->from . ' ' . $result->to;
            }
        }
        $this->processedOutput = $output;

        return $this;
    }
    public function prepareRowsCellsData2()
    {
        $days = DatesHelper::generateBetweenDates($this->config['startDate'], $this->config['endDate']);
        $Shifts = [];
        $counter = 0;
        $shiftsList = [];     
       // echo('<pre>');var_dump($this->processedOutput); die; 
        foreach($this->processedOutput as $shift_id => $shift)
        {
            $Shifts[$counter] = [];
            for ($i = 0; $i < count($days); $i++) {
                $Shifts[$counter][$i]=[];
                $Shifts[$counter][$i] = array_merge($Shifts[$counter][$i], $shift[$days[$i]] );
                $Shifts[$counter][$i] = $Shifts[$counter][$i] == null ? [] : $Shifts[$counter][$i];
                
            }
            $Shifts[$counter] =  $Shifts[$counter] == NULL ? [] :  $Shifts[$counter];
             

            //$ar = $Shifts[$counter];
            
            $Shifts[$counter] = array_map(null, ...$Shifts[$counter]);

            $counter++;
            $shiftsList[] = $shift_id;
        }
        return [$Shifts, $shiftsList];
        
        $ar = $Shifts[11];
        
        
      
       
    }
    public function prepareRowsCellsData()
    {
        $days = DatesHelper::generateBetweenDates($this->config['startDate'], $this->config['endDate']);
        $rows = [];
        $ShiftsPerDay = [];
        for ($i = 0; $i < count($days); $i++) {
            //$ShiftsPerDay[$i] = [$days[$i]];
            $ShiftsPerDay[$i] = [];
            $ShiftsPerDay[$i] = array_merge($ShiftsPerDay[$i], $this->processedOutput[11][$days[$i]]);
            if (!$ShiftsPerDay[$i]) {
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
        //echo('<pre>'); var_dump($ShiftsPerDay, $r ); die;
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

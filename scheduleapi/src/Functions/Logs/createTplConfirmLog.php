<?php

namespace App\Functions\Logs;

use App\Functions\Interfaces\ILogs;
use App\Constants\AgentConstants;
use App\Functions\DatesHelper;
use WHMCS\Database\Capsule as DB;
class createTplConfirmLog implements ILogs
{
    public $tplid, $dates, $overwrite;
    public function __construct($tplid, $datesParsed, $overwrite)
    {
        $this->tplid = $tplid;
        $this->dates = $datesParsed;
        $this->overwrite = $overwrite;
    }
    public function getLog()
    {
        $tpl_details = DB::table('schedule_templates as t')->join('schedule_agentsgroups as g', 'g.id', '=', 't.group_id')->first(['g.group', 't.name']);
        $overwritten = $this->overwrite ? ' with overwriting ' : '';
        $log_entry = 'Inserting template ' . $this->tplid . ' ' . $tpl_details->name . '' . $overwritten . '(' . $tpl_details->group . ') for ' . $this->dates[0] . '-' . $this->dates[1];
        $log = ['author' => AgentConstants::adminid(), 'log' => $log_entry, 'action' => 'Added', 'date' => DB::raw('NOW()')];

        return $log;
    }
}

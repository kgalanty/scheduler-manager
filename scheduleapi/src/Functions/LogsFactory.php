<?php

namespace App\Functions;

use WHMCS\Database\Capsule as DB;
use App\Functions\Interfaces\ILogs;
class LogsFactory
{
    public $log;
    public function __construct(ILogs $log)
    {
        $this->log = $log->getLog();
    }
    public function store()
    {
        DB::table('schedule_eventslog')->insert($this->log);
    }
}
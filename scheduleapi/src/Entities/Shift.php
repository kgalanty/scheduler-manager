<?php
namespace App\Entities;

use WHMCS\Database\Capsule as DB;

class Shift 
{
    public $row;

    public function __construct(int $id)
    { 
        if($id)
        {
            $this->row = DB::table('schedule_shifts')->where('id', $id)->first();
        }
    }

    public function isOnCall()
    {
        return $this->row->from === 'on' && $this->row->to === 'call';
    }
}
<?php
namespace App\Entities;

use App\Functions\Interfaces\IEntity;
use WHMCS\Database\Capsule as DB;

class VacationRequest implements IEntity
{
    public $row;

    public function __construct(int $id)
    { 
        if($id)
        {
            $this->row = DB::table('schedule_vacations_request')->where('id', $id)->first();
        }
    }

    public function getRow()
    {
        return $this->row;
    }
    
    public function setRowAttr(string $attr, $value)
    {
        $this->getRow()->{$attr} = $value;
    }

    public function isAlreadyApproved()
    {
        return 
    }
}
<?php

namespace App\Functions;

class DaysOffType
{
    public $requestType;
    public function __construct(int $requestType)
    {
        $this->requestType = $requestType;
    }

    public function giveType()
    {
        switch ($this->requestType) {
            case 1:
                return 'vacation';
            case 2:
                return 'shift change';
            case 3:
                return 'sick leave';
        }
    }
}

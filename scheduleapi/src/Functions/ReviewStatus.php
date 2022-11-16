<?php

namespace App\Functions;

class ReviewStatus
{
    public $reviewStatus;
    public function __construct(int $reviewStatus)
    {
        $this->reviewStatus = $reviewStatus;
    }

    public function giveStatus()
    {
        switch ($this->reviewStatus) {
            case 0:
                return 'not reviewed yet';
            case 1:
                return 'accepted';
            case 2:
                return 'rejected';
        }
    }
}

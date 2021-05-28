<?php

namespace App\Constants;

class AgentConstants
{
    public static function adminid()
    {
        return 230;
        return $_SESSION['adminid'];
    }
    const EDITOR_PERMISSION = 3100;
}
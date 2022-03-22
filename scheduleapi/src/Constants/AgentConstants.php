<?php

namespace App\Constants;

class AgentConstants
{
    public static function adminid()
    {
        return $_SESSION['adminid'] ?? 230;
    }
    const EDITOR_PERMISSION = 3100;
}
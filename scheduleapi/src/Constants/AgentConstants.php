<?php

namespace App\Constants;
use WHMCS\Database\Capsule as DB;

class AgentConstants
{
    public static function adminid()
    {
        //return 98;
        if(strpos($_SERVER['HTTP_REFERER'], 'localhost') !== false)
        {
            return 61;
        }
        // if($_SESSION['adminid'] === 230)
        // {
        //     return 223;
        // }
        return $_SESSION['adminid'];
    }
    public static function adminname()
    {
        if(!$_SESSION['adminname'])
        {
            $query = DB::table('tbladmins')->where('id', self::adminid())->first(['firstname', 'lastname']);
            $_SESSION['adminname'] = $query->firstname.' '.$query->lastname;

        }

        return $_SESSION['adminname'];
    }
    const EDITOR_PERMISSION = 3100;
}
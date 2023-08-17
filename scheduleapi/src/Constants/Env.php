<?php

namespace App\Constants;

class Env
{
    public static function api()
    {
        global $CONFIG;
        return $CONFIG['SystemURL'].'/schedule/scheduleapi'; //https://my.tmdhosting.com/schedule/scheduleapi';
}
}
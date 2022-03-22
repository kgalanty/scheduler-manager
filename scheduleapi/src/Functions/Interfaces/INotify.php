<?php

namespace App\Functions\Interfaces;

interface INotify
{
    public function fetchAgents();
    public function send($api);
}
<?php
function ScheduleManager_config() {
    $configarray = array(
    "name" => "Schedule Manager",
    "description" => "",
    "version" => "1.0",
    "author" => "TMD",
   );
    return $configarray;
}

function ScheduleManager_activate(){}

function ScheduleManager_output($vars) {

header('Location: ../schedule');

    echo '<p>Redirecting...</p>';

}
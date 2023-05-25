<?php
//Display on call shift on topbar
use WHMCS\Database\Capsule as DB;
add_hook('AdminAreaHeaderOutput', 1, function($vars) {

	$shiftid = DB::table('tblconfiguration as c')->where('c.setting', 'ScheduleManagerTopBarShift')->value('value');

	if($shiftid)
	{
			$oncallperson = DB::table('schedule_timetable as t')
			->join('schedule_slackusers as su', 'su.agent_id', '=', 't.agent_id')
			->where('shift_id', $shiftid)
			->where('day', date('Y-m-d'))
			->first();

			/*
							object(stdClass)#183 (13) {
			  ["id"]=>
			  int(176)
			  ["agent_id"]=>
			  int(280)
			  ["group_id"]=>
			  int(16)
			  ["shift_id"]=>
			  int(44)
			  ["day"]=>
			  string(10) "2022-03-29"
			  ["draft"]=>
			  int(0)
			  ["author"]=>
			  int(230)
			  ["order"]=>
			  int(1)
			  ["phone"]=>
			  string(16) "+359884 59 60 87"
			  ["email"]=>
			  string(30) "dimitar.terziev@tmdhosting.com"
			  ["slackid"]=>
			  string(11) "U017W535890"
			  ["realname"]=>
			  string(15) "Dimitar Terziev"
			  ["realnamenormalized"]=>
			  string(15) "Dimitar Terziev"
			}
				
			*/
			
	}
if($oncallperson && $oncallperson->phone)
{

	$return = <<<JS
	<script>
		$(function() { 
			$('#logout').after(' |  <span style="background: #ff3e3e;padding: 4px;border-radius: 5px;color: white;">On Call Today: {$oncallperson->phone}</span>')

		})


	</script>
JS;
	return $return;
	}
});

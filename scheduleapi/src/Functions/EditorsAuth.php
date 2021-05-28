<?php

namespace App\Functions;

use WHMCS\Database\Capsule as DB;
use Illuminate\Support\Collection;
use App\Constants\AgentConstants;
class EditorsAuth
{
	public static function isEditor()
	{
		return DB::table('schedule_editors as e')->where('e.editor_id', AgentConstants::adminid())->count() == 0 ? false : true;
	}

}

<?php

namespace App\Controllers;

use WHMCS\Database\Capsule as DB;
use App\Responses\Response;

class SubteamsController
{
    public function setcolor($request, $response, $args)
    {
        $color_field = $request->getParsedBody()['color'];
        $color_value = $request->getParsedBody()['value'];
        $groupid = $request->getParsedBody()['groupid'];

    
        $results = DB::table("schedule_agentsgroups as t")
        ->where('id',$groupid)
        ->update([$color_field => $color_value]);
          

        return Response::json(['response' => 'success', 'query' => $results], $response);
    }
}

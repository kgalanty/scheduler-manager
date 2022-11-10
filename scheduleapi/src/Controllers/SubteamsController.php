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
            ->where('id', $groupid)
            ->update([$color_field => $color_value]);


        return Response::json(['response' => 'success', 'query' => $results], $response);
    }
    public function reorder($request, $response, $args)
    {
        $id = $request->getParsedBody()['id'];
        $direction = $args['direction'];
        $entry = DB::table('schedule_agentsgroups as ag')
            ->where('id', $id)
            ->first();
        if ($direction == 'up') {
            $entryExchange = DB::table('schedule_agentsgroups as ag')
                ->where('order', $entry->order - 1)
                ->where('parent', $entry->parent)
                ->first();

            DB::table('schedule_agentsgroups as ag')
                ->where('id', $entryExchange->id)
                ->where('parent', $entry->parent)
                ->update(['order' => $entryExchange->order + 1]);

            DB::table('schedule_agentsgroups as ag')
                ->where('id', $entry->id)
                ->where('parent', $entry->parent)
                ->update(['order' => $entry->order - 1]);
            //$entryExchange

            return Response::json(['response' => 'success'], $response);
        }
        elseif ($direction == 'down') {
            $entryExchange = DB::table('schedule_agentsgroups as ag')
                ->where('order', $entry->order + 1)
                ->where('parent', $entry->parent)
                ->first();

            DB::table('schedule_agentsgroups as ag')
                ->where('id', $entryExchange->id)
                ->where('parent', $entry->parent)
                ->update(['order' => $entryExchange->order - 1]);

            DB::table('schedule_agentsgroups as ag')
                ->where('id', $entry->id)
                ->where('parent', $entry->parent)
                ->update(['order' => $entry->order + 1]);
            //$entryExchange

            return Response::json(['response' => 'success'], $response);
        }
        // $results = DB::table("schedule_agentsgroups as t")
        // ->where('id',$groupid)
        // ->update([$color_field => $color_value]);
    }
}

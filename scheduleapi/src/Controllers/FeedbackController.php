<?php

namespace App\Controllers;

use App\Constants\AgentConstants;
use App\Functions\AgentsHelper;
use App\Functions\DatesHelper;
use App\Functions\DaysOffHelper;
use App\Functions\EditorsAuth;
use WHMCS\Database\Capsule as DB;
use App\Responses\Response;

class FeedbackController
{
    public function submitrequest($request, $response, $args)
    {
        $author =  AgentConstants::adminid();
        $feedback = $request->getParsedBody()['comment'];

        $results = DB::table("schedule_feedback")
            ->insert([
                'author_id' => $author,
                'feedback' => $feedback,
                'date' => gmdate('Y-m-d H:i:s'),
            ]);

        return Response::json(['response' => 'success', 'query' => $results], $response);
    }
}

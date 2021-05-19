<?php

namespace App\Controllers;
use WHMCS\Database\Capsule as DB;
class AgentsController
{
    public function agents($request, $response, $args)
    {
          $data = [];
    $data = DB::table("tbladmins")->get(['id', 'firstname', 'lastname', 'username']);
    $payload = json_encode($data);
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}

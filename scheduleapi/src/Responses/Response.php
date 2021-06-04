<?php

namespace App\Responses;

class Response implements IResponse
{
    public static function json(array $data = [], $response)
    {
        $payload = json_encode($data);
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}

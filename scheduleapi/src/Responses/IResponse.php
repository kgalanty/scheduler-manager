<?php
namespace App\Responses;

interface IResponse
{
    public static function json(array $data = [], $response);
}
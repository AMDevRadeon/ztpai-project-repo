<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;


class UniformResponse
{
    public static function createInvalid(string $desc, int $status = Response::HTTP_BAD_REQUEST): array
    {
        return [
            'desc' => "${desc}",
            'status' => $status
        ];
    }

    public static function createValid(string $desc, mixed $body = NULL, int $status = Response::HTTP_OK): array
    {
        $json = [
            'desc' => "${desc}",
            'status' => $status,
        ];


        if ($body !== NULL)
        {
            $json['value'] = $body;
        }
        
        return $json;
    }
}
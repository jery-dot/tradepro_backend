<?php

namespace App\Helpers;

class ApiResponse
{
    public static function success($message, $data = [], $statusCode = 200, $meta = [])
    {
        if(count($meta)){
            return response()->json(array_merge([
                'status'  => true,
                'message' => $message,
            ], $data, $meta), $statusCode);
        }
        return response()->json(array_merge([
            'status'  => true,
            'message' => $message,
        ], $data), $statusCode);
    }

    public static function warning($message, $statusCode = 401)
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
        ], $statusCode);
    }
    public static function error($message, $statusCode = 400, $errors = [])
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'errors'  => $errors,
        ], $statusCode);
    }
}


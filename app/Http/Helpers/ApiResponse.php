<?php

namespace App\Http\Helpers;

trait ApiResponse
{
    private function apiResponse($code = 200, $message = null, $errors = null, $data = null)
    {
        $array = [
            'status' => $code,
            'message' => $message,

        ];

        if (!is_null($errors)) {
            $array['errors'] = $errors;
        }

        if (!is_null($data)) {
            $array['data'] = $data;
        }

        return response($array, $code);
    }
}

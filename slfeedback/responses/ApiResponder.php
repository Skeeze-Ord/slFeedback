<?php

declare(strict_types=1);

namespace Sells\SlFeedback\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponder
{
    public static function success(array $data = [], string $message = 'OK'): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== []) {
            $response['data'] = $data;
        }

        return response()->json($response);
    }

    public static function error(string $message, int $code = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $code);
    }

    public static function validation(array $errors, string $message = 'Ошибка валидации данных'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], 422);
    }
}

<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;

class HttpResponseService
{
    // 200 OK
    public function ok($data, $message = 'Success'): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data
        ], 200);
    }

    // 201 Created
    public function created($data, $message = 'Resource created'): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data
        ], 201);
    }

    // 400 Bad Request
    public function badRequest($message = 'Bad request'): JsonResponse
    {
        return response()->json([
            'error' => $message
        ], 400);
    }

    // 401 Unauthorized
    public function unauthorized($message = 'Unauthorized'): JsonResponse
    {
        return response()->json([
            'error' => $message
        ], 401);
    }

    // 403 Forbidden
    public function forbidden($message = 'Forbidden'): JsonResponse
    {
        return response()->json([
            'error' => $message
        ], 403);
    }

    // 404 Not Found
    public function notFound($message = 'Resource not found'): JsonResponse
    {
        return response()->json([
            'error' => $message
        ], 404);
    }

    // 500 Internal Server Error
    public function serverError($message = 'Internal server error'): JsonResponse
    {
        return response()->json([
            'error' => $message
        ], 500);
    }

    // Any HTTP error custom code
    public function custom($statusCode, $message, $data = null): JsonResponse
    {
        $response = ['message' => $message];
        if ($data) {
            $response['data'] = $data;
        }
        return response()->json($response, $statusCode);
    }
}

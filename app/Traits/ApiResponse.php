<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

trait ApiResponse
{
    // ======================================================================
    // SUCCESSFUL RESPONSES (2xx)
    // ======================================================================

    /**
     * Responds with a 200 OK.
     */
    protected function success(mixed $data = [], ?string $message = 'OK'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], Response::HTTP_OK);
    }

    /**
     * Responds with a 201 Created.
     */
    protected function created(mixed $data = [], ?string $message = 'Resource created'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], Response::HTTP_CREATED);
    }

    /**
     * Responds with a 204 No Content.
     */
    protected function noContent(): JsonResponse
    {
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    // ======================================================================
    // CLIENT ERROR RESPONSES (4xx)
    // ======================================================================

    /**
     * Responds with a 400 Bad Request.
     */
    protected function badRequest(?string $message = 'Bad request', mixed $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Responds with a 401 Unauthorized.
     */
    protected function unauthorized(?string $message = 'Unauthorized'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Responds with a 403 Forbidden.
     */
    protected function forbidden(?string $message = 'Forbidden'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], Response::HTTP_FORBIDDEN);
    }

    /**
     * Responds with a 404 Not Found.
     */
    protected function notFound(?string $message = 'Resource not found'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * Responds with a 422 Unprocessable Entity.
     */
    protected function unprocessable(mixed $errors, ?string $message = 'Validation failed'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    // ======================================================================
    // SERVER ERROR RESPONSES (5xx)
    // ======================================================================

    /**
     * Responds with a 500 Internal Server Error.
     */
    protected function internalError(?string $message = 'An internal server error occurred'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

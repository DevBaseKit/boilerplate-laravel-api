<?php

namespace App\Traits;

use App\Constants\ApiStatusCode;
use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    /**
     * Send standard success response.
     *
     * @param mixed $result
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    public function sendSuccess(
        mixed $result = null,
        string $message = '',
        int $statusCode = ApiStatusCode::OK
    ): JsonResponse
    {
        return response()->json([
            'status' => true,
            'status_code' => $statusCode,
            'message' => $message,
            'result' => $result,
        ], $statusCode);
    }

    /**
     * Send standard error response.
     *
     * @param string $message
     * @param array $errorItems
     * @param int $statusCode
     * @return JsonResponse
     */
    public function sendError(
        string $message,
        array $errorItems = [],
        int $statusCode = ApiStatusCode::BAD_REQUEST
    ): JsonResponse
    {
        return response()->json([
            'status' => false,
            'status_code' => $statusCode,
            'message' => $message,
            'error_items' => $errorItems,
        ], $statusCode);
    }
}

<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

trait ResponseApiTrait
{
    /**
     * responseBadRequest for api
     *
     * @return JsonResponse
     */
    protected function responseBadRequest(string $message = ''): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * responseSuccess for api
     *
     * @return array
     */
    protected function responseSuccess($data, string $message = ''): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
            'status' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }

    /**
     * responseNotFound for api
     *
     * @return JsonResponse
     */
    protected function responseNotFound(string $message = ''): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'status' => Response::HTTP_NOT_FOUND
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * responseUnAuthorized for api
     *
     * @return JsonResponse
     */
    protected function responseUnAuthorized(): JsonResponse
    {
        return response()->json([
            'message' => __('auth.message.login.error'),
            'status' => Response::HTTP_UNAUTHORIZED
        ], Response::HTTP_UNAUTHORIZED);
    }
}

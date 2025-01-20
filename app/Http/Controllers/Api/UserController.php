<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\DefaultController;
use App\Services\Api\UserService;
use Illuminate\Support\Facades\Log;

class UserController extends DefaultController
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function show()
    {
        try {
            Log::info("CloudWatch Start");
            $response = $this->userService->getUserInfoById(1);
            Log::info("Response", ['response' => $response]);

            if ($response['status']) {
                return $this->responseSuccess(
                    $response['data'],
                    __('common.success_message')
                );
            }
            return $this->responseBadRequest();
        } catch (\Exception $e) {
            Log::error("Error en show: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function getUser()
    {
        $response = $this->userService->getAll();
        if ($response['status']) {
            return $this->responseSuccess(
                $response['data'],
                __('common.success_message')
            );
        }
        return $this->responseBadRequest();
    }
}

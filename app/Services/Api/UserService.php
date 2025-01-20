<?php

namespace App\Services\Api;

use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use App\Repositories\Interfaces\UserRepositoryInterface;

/**
 * Class UserService
 * @package App\Services
 */
class UserService extends BaseService
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
    ) {
        $this->userRepository = $userRepository;
    }

    public function getUserInfoById(int $id)
    {
        $user = $this->userRepository->getUserById($id);
        if($user) {
           return $this->responseData(true, new UserResource($user));
        }
        return $this->responseData(false);
    }

    public function getAll()
    {
        $users = $this->userRepository->paginate(10);
        return $this->responseData(true, new UserCollection($users));

    }
}

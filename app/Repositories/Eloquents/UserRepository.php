<?php


namespace App\Repositories\Eloquents;

use App\Repositories\Eloquents\BaseRepository;
use App\Repositories\Interfaces\UserRepositoryInterface;

use App\Models\User;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $user)
    {
        parent::__construct($user);
    }

    public function getUserById(int $id) {
      return $this->model->where('id', $id)->first();
    }
}
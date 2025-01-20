<?php

namespace App\Repositories\Interfaces;

interface BaseRepositoryInterface
{
    public function get();

    public function paginate(int $page);

    public function find($id);

    public function create(array $data);

    public function update($id, array $data);

    public function delete($id);
}

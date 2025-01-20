<?php

namespace App\Traits;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

trait WebResponse
{
    protected function getResponse(
        string $status,
        string $message,
        array|Collection|Paginator $data = []
    ) {
        return [
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ];
    }
}

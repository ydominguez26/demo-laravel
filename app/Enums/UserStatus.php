<?php

namespace App\Enums;

enum UserStatus: int
{
    case ACTIVE = 1;
    case INACTIVE = 2;

    public function isActive(): bool
    {
        return $this == self::ACTIVE;
    }
}

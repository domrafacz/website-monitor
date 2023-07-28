<?php

declare(strict_types=1);

namespace App\Enum;
enum UserStatus: int
{
    case ACTIVE = 1;
    case INACTIVE = 2;
    case BLOCKED = 3;
}

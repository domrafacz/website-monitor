<?php

declare(strict_types=1);

namespace App\Tests\Unit\Traits;

use App\Entity\User;
use App\Enum\UserStatus;

trait UserTrait
{
    public function createUser(
        int $id,
        string $identifier = 'test@test.com',
        UserStatus $status = UserStatus::ACTIVE
    ): User {
        $user = new class ($id, $status) extends User {
            public function __construct(int $id, UserStatus $status)
            {
                parent::__construct();
                $this->id = $id;
                $this->setStatus($status);
            }

            public function getId(): int
            {
                return $this->id;
            }
        };

        $user->setEmail($identifier);

        return $user;
    }
}

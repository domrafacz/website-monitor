<?php

declare(strict_types=1);

namespace App\Tests\Unit\Traits;

use App\Entity\User;

trait UserTrait
{
    public function createUser(
        int $id,
        string $identifier = 'test@test.com',
    ): User {
        $user = new class ($id) extends User {
            public function __construct(int $id)
            {
                parent::__construct();
                $this->id = $id;
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

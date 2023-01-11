<?php

declare(strict_types=1);

namespace App\Tests\Unit\Traits;

use App\Entity\NotifierChannel;
use App\Entity\User;

trait NotifierChannelTrait
{
    public function createNotifierChannel(
        int $id,
        User $user,
        int $type = 0,
        string $name = 'test',
    ): NotifierChannel {
        return new class ($id, $user, $type, $name) extends NotifierChannel {
            public function __construct(int $id, User $user, int $type, string $name)
            {
                parent::__construct($user, $type, $name);
                $this->id = $id;
            }

            public function getId(): int
            {
                return $this->id;
            }
        };
    }
}

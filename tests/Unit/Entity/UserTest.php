<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\NotifierChannel;
use App\Entity\User;
use App\Entity\Website;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testSetterAndGetter(): void
    {
        $user = new class () extends User {
            public function getId(): int
            {
                return 1;
            }
        };

        $website = new class () extends Website {
            public function getId(): int
            {
                return 1;
            }
        };

        $notifierChannel = new class ($user, 0, 'test') extends NotifierChannel {
            public function getId(): int
            {
                return 1;
            }
        };

        $this->assertNull((new User())->getId());
        $this->assertSame(1, $user->getId());

        $this->assertInstanceOf(User::class, $user->setEmail('test@test.com'));
        $this->assertSame('test@test.com', $user->getEmail());
        $this->assertSame('test@test.com', $user->getUserIdentifier());

        $this->assertInstanceOf(User::class, $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']));
        $this->assertSame(['ROLE_USER', 'ROLE_ADMIN'], $user->getRoles());

        $this->assertInstanceOf(User::class, $user->setPassword('test_password'));
        $this->assertSame('test_password', $user->getPassword());

        $this->assertInstanceOf(User::class, $user->setLanguage('pl'));
        $this->assertSame('pl', $user->getLanguage());

        $this->assertInstanceOf(User::class, $user->addWebsite($website));
        $this->assertSame(1, $user->getWebsites()->count());

        $this->assertInstanceOf(Website::class, $user->findWebsite(1));
        $this->assertNull($user->findWebsite(2));

        $this->assertInstanceOf(User::class, $user->removeWebsite($website));
        $this->assertSame(0, $user->getWebsites()->count());

        $this->assertInstanceOf(User::class, $user->addNotifierChannel($notifierChannel));
        $this->assertSame(1, $user->getNotifierChannels()->count());

        $this->assertInstanceOf(NotifierChannel::class, $user->findNotifierChannel(1));
        $this->assertNull($user->findNotifierChannel(2));

        $this->assertInstanceOf(User::class, $user->removeNotifierChannel($notifierChannel));
        $this->assertSame(0, $user->getNotifierChannels()->count());

        $this->assertInstanceOf(User::class, $user->setQuota(20));
        $this->assertSame(20, $user->getQuota());
    }
}

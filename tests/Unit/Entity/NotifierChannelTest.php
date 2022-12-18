<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\NotifierChannel;
use App\Entity\User;
use App\Entity\Website;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class NotifierChannelTest extends TestCase
{
    public function testDefaults(): void
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

        $notifierChannel = new NotifierChannel(
            $user,
            0,
            'test',
            ['test' => 1],
            new ArrayCollection([$website])
        );

        $this->assertNull($notifierChannel->getId());
        $this->assertSame($user, $notifierChannel->getOwner());
        $this->assertSame(0, $notifierChannel->getType());
        $this->assertSame(['test' => 1], $notifierChannel->getOptions());
        $this->assertEquals(new ArrayCollection([$website]), $notifierChannel->getWebsites());
        $this->assertSame('test', $notifierChannel->getName());
    }

    public function testSetterAndGetter(): void
    {
        $user = new class () extends User {
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

        $website = new class () extends Website {
            public function getId(): int
            {
                return 1;
            }
        };

        $this->assertSame(1, $notifierChannel->getId());
        $this->assertNull((new NotifierChannel($user, 0, 'test'))->getId());

        $this->assertInstanceOf(NotifierChannel::class, $notifierChannel->setOwner($user));
        $this->assertSame($user, $notifierChannel->getOwner());

        $this->assertInstanceOf(NotifierChannel::class, $notifierChannel->setType(2));
        $this->assertSame(2, $notifierChannel->getType());

        $this->assertNull($notifierChannel->getOptions());
        $this->assertInstanceOf(NotifierChannel::class, $notifierChannel->setOptions(['test' => 1]));
        $this->assertSame(['test' => 1], $notifierChannel->getOptions());

        $this->assertInstanceOf(NotifierChannel::class, $notifierChannel->addWebsite($website));
        $this->assertSame(1, $notifierChannel->getWebsites()->count());

        $this->assertInstanceOf(NotifierChannel::class, $notifierChannel->removeWebsite($website));
        $this->assertSame(0, $notifierChannel->getWebsites()->count());

        $this->assertInstanceOf(NotifierChannel::class, $notifierChannel->setName('new_name'));
        $this->assertSame('new_name', $notifierChannel->getName());
    }
}

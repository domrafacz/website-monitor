<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\NotifierChannel;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UserManagerTest extends TestCase
{
    public function testGetCurrentUserReturnNull(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $security = $this->createStub(Security::class);

        $security->method('getUser')->willReturn(null);
        $userManager = new UserManager($userRepository, $passwordHasher, $security);
        $this->expectException(UserNotFoundException::class);
        $userManager->getCurrentUser();
    }

    public function testGetCurrentUserReturnWrongInstance(): void
    {
        $userMock = $this->createMock(UserInterface::class);
        $userRepository = $this->createMock(UserRepository::class);
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $security = $this->createMock(Security::class);
        $security
            ->method('getUser')
            ->will($this->returnValue($userMock));

        $userManager = new UserManager($userRepository, $passwordHasher, $security);
        $this->expectException(UnexpectedTypeException::class);
        $userManager->getCurrentUser();
    }

    public function testGetNotifierChannel(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $security = $this->createMock(Security::class);
        $userManager = new UserManager($userRepository, $passwordHasher, $security);

        $user = new class () extends User {
            public function getId(): int
            {
                return 10;
            }
        };

        $channel = new class ($user, 0, 'test') extends NotifierChannel {
            public function getId(): int
            {
                return 20;
            }
        };

        $user->addNotifierChannel($channel);
        $channel = $userManager->getNotifierChannel($user, 20);

        $this->assertEquals(20, $channel->getId());
    }
    public function testGetNotifierChannelWrongId(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $security = $this->createMock(Security::class);
        $userManager = new UserManager($userRepository, $passwordHasher, $security);

        $user = new class () extends User {
            public function getId(): int
            {
                return 10;
            }
        };

        $channel = new class ($user, 0, 'test') extends NotifierChannel {
            public function getId(): int
            {
                return 20;
            }
        };

        $user->addNotifierChannel($channel);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Channel not found, id: 999');

        $userManager->getNotifierChannel($user, 999);
    }
}

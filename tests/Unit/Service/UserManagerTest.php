<?php

namespace App\Tests\Unit\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserManager as UserManagerService;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UserManagerTest extends KernelTestCase
{
    public function testGetCurrentUserReturnNull(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $security = $this->createStub(Security::class);

        $security->method('getUser')->willReturn(null);
        $userManager = new UserManagerService($userRepository, $passwordHasher, $security);
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

        $userManager = new UserManagerService($userRepository, $passwordHasher, $security);
        $this->expectException(UnexpectedTypeException::class);
        $userManager->getCurrentUser();
    }
}
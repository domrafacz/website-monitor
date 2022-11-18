<?php

namespace App\Tests\Unit\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserManager as UserManagerService;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Security;

class UserManager extends KernelTestCase
{
    public function testWtf(): void
    {
        $this->assertEquals(1, 2);
    }

    public function testGetCurrentUser(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $security = $this->getMockBuilder(Security::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes();



        $stub = $this->createStub(Security::class);

        $stub->method('getUser')
            ->willReturn(null);

        $userManager = new UserManagerService($userRepository, $passwordHasher, $stub);
        $this->expectException(UserNotFoundException::class);

        $userManager->getCurrentUser();
        echo 'xd123dsdas';
        $invalidObject = new \stdClass();
    }
}
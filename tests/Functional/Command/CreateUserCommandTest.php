<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class CreateUserCommandTest extends KernelTestCase
{
    private Command $command;

    public function setUp(): void
    {
        $application = new Application(self::bootKernel());
        $this->command = $application->find('app:user:create');
    }

    public function testCreateUser(): void
    {
        $commandTester = new CommandTester($this->command);

        $commandTester->execute([
            'email' => 'test2@test.com',
            'password' => 'Test123#'
        ]);

        $commandTester->assertCommandIsSuccessful();
        $this->assertStringContainsString('User has been added!', $commandTester->getDisplay());
    }

    public function testCreateUserWithTakenUsername(): void
    {
        $commandTester = new CommandTester($this->command);

        $commandTester->execute([
            'email' => 'test1@test.com',
            'password' => 'Test123#'
        ]);

        $this->assertStringContainsString('Given email is already taken!', $commandTester->getDisplay());
    }

    public function testCreateUserWithWeakPassword(): void
    {
        $commandTester = new CommandTester($this->command);

        $commandTester->execute([
            'email' => 'test2@test.com',
            'password' => 'weak'
        ]);

        $this->assertStringContainsString('Given password is not strong enough!', $commandTester->getDisplay());
    }

    public function testCreateUserWithAdminRole(): void
    {
        $commandTester = new CommandTester($this->command);

        $commandTester->execute([
            'email' => 'test_admin@test.com',
            'password' => 'Test123#',
            'admin' => 'admin'
        ]);

        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $admin = $userRepository->findOneByUsername('test_admin@test.com');

        $commandTester->assertCommandIsSuccessful();
        $this->assertStringContainsString('User has been added!', $commandTester->getDisplay());
        $this->assertTrue(in_array('ROLE_ADMIN', $admin->getRoles()));
    }
}

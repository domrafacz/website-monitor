<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ChangePasswordCommandTest extends KernelTestCase
{
    private Command $command;

    public function setUp(): void
    {
        $application = new Application(self::bootKernel());
        $this->command = $application->find('app:user:change-password');
    }

    public function testChangePasswordSuccessfully(): void
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->setInputs(['NewPass1#', 'NewPass1#']);

        $commandTester->execute(['email' => 'test1@test.com']);

        $commandTester->assertCommandIsSuccessful();
        $this->assertStringContainsString(
            'Password for user "test1@test.com" has been changed successfully.',
            $commandTester->getDisplay()
        );
    }

    public function testNewPasswordIsActuallyHashed(): void
    {
        $newPassword = 'NewPass1#';

        $commandTester = new CommandTester($this->command);
        $commandTester->setInputs([$newPassword, $newPassword]);
        $commandTester->execute(['email' => 'test1@test.com']);

        $commandTester->assertCommandIsSuccessful();

        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByUsername('test1@test.com');

        /** @var UserPasswordHasherInterface $hasher */
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $this->assertNotNull($user);
        $this->assertTrue($hasher->isPasswordValid($user, $newPassword));
    }

    public function testChangePasswordForNonExistentUser(): void
    {
        $commandTester = new CommandTester($this->command);

        $commandTester->execute(['email' => 'nonexistent@test.com']);

        $this->assertSame(Command::FAILURE, $commandTester->getStatusCode());
        $this->assertStringContainsString(
            'User with email "nonexistent@test.com" not found.',
            $commandTester->getDisplay()
        );
    }

    public function testChangePasswordWithMismatchedPasswords(): void
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->setInputs(['NewPass1#', 'Different1#']);

        $commandTester->execute(['email' => 'test1@test.com']);

        $this->assertSame(Command::FAILURE, $commandTester->getStatusCode());
        $this->assertStringContainsString('Passwords do not match.', $commandTester->getDisplay());
    }

    public function testChangePasswordWithWeakPassword(): void
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->setInputs(['weak', 'weak']);

        $commandTester->execute(['email' => 'test1@test.com']);

        $this->assertSame(Command::FAILURE, $commandTester->getStatusCode());
        $this->assertStringContainsString('Given password is not strong enough!', $commandTester->getDisplay());
    }

    public function testChangePasswordWithEmptyPassword(): void
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->setInputs(['', '']);

        $commandTester->execute(['email' => 'test1@test.com']);

        $this->assertSame(Command::FAILURE, $commandTester->getStatusCode());
        $this->assertStringContainsString('Password cannot be empty.', $commandTester->getDisplay());
    }
}


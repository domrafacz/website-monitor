<?php
declare(strict_types=1);

namespace App\Tests\Functional\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CreateUserCommandTest extends KernelTestCase
{
    public function testCreateUser(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('app:user:create');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'email' => 'test2@test.com',
            'password' => 'Test123#'
        ]);

        $commandTester->assertCommandIsSuccessful();
        $this->assertStringContainsString('User has been added!', $commandTester->getDisplay());
    }
}
<?php
declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DashboardControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $userRepository;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
    }

    public function testDashboardWebsites(): void
    {
        $testUser = $this->userRepository->findOneByUsername('test1@test.com');
        $this->client->loginUser($testUser);
        $this->client->request('GET', '/websites');
        $this->assertResponseIsSuccessful();
    }
}


<?php
declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use \Symfony\Bundle\FrameworkBundle\KernelBrowser;

class HomepageControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
    }

    protected function tearDown(): void
    {
        unset($this->userRepository);
        unset($this->client);
        parent::tearDown();
    }

    public function testAuthenticatedRedirect(): void
    {
        $testUser = $this->userRepository->findOneByUsername('test1@test.com');
        $this->client->loginUser($testUser);

        $this->client->request('GET', '/');
        $this->assertResponseRedirects('/dashboard');
    }

    public function testUnauthenticatedRedirect(): void
    {
        $this->client->request('GET', '/');
        $this->assertResponseRedirects('/en/login');
    }
}

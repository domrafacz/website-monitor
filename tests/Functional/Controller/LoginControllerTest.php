<?php
declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use \Symfony\Bundle\FrameworkBundle\KernelBrowser;

class LoginControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $userRepository;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
    }

    public function testUnauthenticatedRedirect(): void
    {
        $this->client->request('GET', '/login');
        $this->assertResponseRedirects('/en/login');
    }

    public function testLoginForm(): void
    {
        $crawler = $this->client->request('GET', '/en/login');
        $form = $crawler->filter('#login_submit')->form();
        $this->client->submit($form, [
            '_username'    => 'test1@test.com',
            '_password' => 'Test123#',
            '_remember_me' => 'on'
        ]);

        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testAuthenticatedRedirect(): void
    {
        $testUser = $this->userRepository->findOneByUsername('test1@test.com');
        $this->client->loginUser($testUser);

        $this->client->request('GET', '/en/login');
        $this->assertResponseRedirects('/dashboard');
    }
}

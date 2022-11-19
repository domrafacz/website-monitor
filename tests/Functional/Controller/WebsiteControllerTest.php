<?php
declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WebsiteControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $userRepository;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
    }

    public function testAddWebsite(): void
    {
        $testUser = $this->userRepository->findOneByUsername('test1@test.com');
        $this->client->loginUser($testUser);

        $crawler = $this->client->request('GET', '/website/add');
        $form = $crawler->filter('#add_website_add')->form();
        $this->client->submit($form, [
            'add_website[url]' => 'https://google.com',
        ]);

        $testUser = $this->userRepository->findOneByUsername('test1@test.com');
        $this->assertEquals(1, $testUser->getWebsites()->count());
    }
}
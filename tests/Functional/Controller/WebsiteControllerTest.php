<?php
declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Repository\UserRepository;
use App\Repository\WebsiteRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WebsiteControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $userRepository;
    private WebsiteRepository $websiteRepository;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
        $this->websiteRepository = static::getContainer()->get(WebsiteRepository::class);
    }

    public function testAddWebsite(): void
    {
        $testUser = $this->userRepository->findOneByUsername('test1@test.com');
        $this->client->loginUser($testUser);
        $originalCount = $testUser->getWebsites()->count();

        $crawler = $this->client->request('GET', '/website/add');
        $form = $crawler->filter('#add_website_add')->form();
        $this->client->submit($form, [
            'add_website[url]' => 'https://google.com',
        ]);

        $testUser = $this->userRepository->findOneByUsername('test1@test.com');
        $this->assertEquals($originalCount+1, $testUser->getWebsites()->count());
    }

    public function testEditWebsite(): void
    {
        $testUser = $this->userRepository->findOneByUsername('test1@test.com');
        $this->client->loginUser($testUser);

        $website = $testUser->getWebsites()->first();

        $crawler = $this->client->request('GET', '/website/edit/'.$website->getId());
        $form = $crawler->filter('#add_website_add')->form();
        $this->client->submit($form, [
            'add_website[url]' => $website->getUrl().'m',
        ]);

        $updatedWebsite = $this->websiteRepository->find($website->getId());
        $this->assertNotEquals($website->getUrl(), $updatedWebsite->getUrl());
    }

    public function testEditWebsiteInvalidId(): void
    {
        $testUser = $this->userRepository->findOneByUsername('test1@test.com');
        $this->client->loginUser($testUser);

        $secondUser = $this->userRepository->findOneByUsername('test11@test.com');
        $this->client->request('GET', '/website/edit/'.$secondUser->getWebsites()->first()->getId());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDeleteWebsite(): void
    {
        $testUser = $this->userRepository->findOneByUsername('test1@test.com');
        $this->client->loginUser($testUser);

        $website = $testUser->getWebsites()->first();

        $crawler = $this->client->request('GET', '/website/details/'.$website->getId());
        $form = $crawler->filter('#delete_website_submit')->form();
        $this->client->submit($form, [
            'delete_website[config]' => 1,
        ]);

        $deletedWebsite = $this->websiteRepository->find($website->getId());
        $this->assertNull($deletedWebsite);
    }

    public function testWebsiteDetailsInvalidId(): void
    {
        $testUser = $this->userRepository->findOneByUsername('test1@test.com');
        $this->client->loginUser($testUser);

        $secondUser = $this->userRepository->findOneByUsername('test11@test.com');
        $this->client->request('GET', '/website/details/'.$secondUser->getWebsites()->first()->getId());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testWebsiteIncidents(): void
    {
        $testUser = $this->userRepository->findOneByUsername('test1@test.com');
        $this->client->loginUser($testUser);

        $this->client->request('GET', '/website/incidents/'.$testUser->getWebsites()->first()->getId());

        $this->assertResponseIsSuccessful();
    }

    public function testWebsiteIncidentsInvalidId(): void
    {
        $testUser = $this->userRepository->findOneByUsername('test1@test.com');
        $this->client->loginUser($testUser);

        $secondUser = $this->userRepository->findOneByUsername('test11@test.com');
        $this->client->request('GET', '/website/incidents/'.$secondUser->getWebsites()->first()->getId());

        $this->assertResponseStatusCodeSame(404);
    }
}
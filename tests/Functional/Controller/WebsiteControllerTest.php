<?php
declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Repository\UserRepository;
use App\Repository\WebsiteRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WebsiteControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $userRepository;
    private WebsiteRepository $websiteRepository;

   protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
        $this->websiteRepository = static::getContainer()->get(WebsiteRepository::class);
    }

    protected function tearDown(): void
    {
        unset($this->userRepository);
        unset($this->websiteRepository);
        unset($this->client);
        parent::tearDown();
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

    public function testWebsiteNotifierChannelToggle(): void
    {
        $testUser = $this->userRepository->findOneByUsername('test1@test.com');
        $this->client->loginUser($testUser);

        $website = $testUser->getWebsites()->first();
        $channelId = $testUser->getNotifierChannels()->first()->getId();

        $this->assertEquals(0, $website->getNotifierChannels()->count());

        $crawler = $this->client->request('GET', '/website/details/'.$website->getId());
        $link = $crawler->filter(sprintf('#channel_toggle_%d', $channelId))->attr('href');

        $this->client->request('GET', $link);

        $updatedWebsite = $this->websiteRepository->find($website->getId());

        $this->assertEquals(1, $updatedWebsite->getNotifierChannels()->count());
    }

    public function testWebsiteNotifierChannelToggleInvalidWebsiteId(): void
    {
        $testUser = $this->userRepository->findOneByUsername('test1@test.com');
        $this->client->loginUser($testUser);

        $this->client->request('GET', 'website/toggle-notifier-channel/931382135/4/none');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testWebsiteNotifierChannelToggleInvalidChannelId(): void
    {
        $testUser = $this->userRepository->findOneByUsername('test1@test.com');
        $this->client->loginUser($testUser);

        $website = $testUser->getWebsites()->first();
        $this->client->request('GET', sprintf('website/toggle-notifier-channel/%d/46813854/none', $website->getId()));

        $this->assertResponseStatusCodeSame(404);
    }

    public function testWebsiteNotifierChannelToggleInvalidCsrfToken(): void
    {
        $testUser = $this->userRepository->findOneByUsername('test1@test.com');
        $this->client->loginUser($testUser);

        $website = $testUser->getWebsites()->first();
        $channelId = $testUser->getNotifierChannels()->first()->getId();

        $this->assertEquals(0, $website->getNotifierChannels()->count());

        $crawler = $this->client->request('GET', '/website/details/'.$website->getId());
        $link = $crawler->filter(sprintf('#channel_toggle_%d', $channelId))->attr('href');

        $this->client->request('GET', mb_substr($link, 0, -5));

        $updatedWebsite = $this->websiteRepository->find($website->getId());

        $this->assertEquals(0, $updatedWebsite->getNotifierChannels()->count());
    }
}
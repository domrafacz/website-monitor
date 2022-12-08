<?php
declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Repository\UserRepository;
use App\Repository\WebsiteRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NotifierControllerTest extends WebTestCase
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

    public function testAddChannelList(): void
    {
        $testUser = $this->userRepository->findOneByUsername('test1@test.com');
        $this->client->loginUser($testUser);

        $this->client->request('GET', '/notifier/add-channel');

        $this->assertResponseIsSuccessful();
    }

    public function testAddTelegramChannel(): void
    {
        $testUser = $this->userRepository->findOneByUsername('test1@test.com');
        $this->client->loginUser($testUser);

        $crawler = $this->client->request('GET', '/notifier/add-channel/0');
        $form = $crawler->filter('#notifier_telegram_channel_submit')->form();

        $this->client->submit($form, [
            'notifier_telegram_channel[name]' => 'telegram_test2',
            'notifier_telegram_channel[apiToken]' => '653',
            'notifier_telegram_channel[chatId]' => '321',
        ]);

        $this->assertEquals(2, $testUser->getNotifierChannels()->count());
        $this->assertResponseStatusCodeSame(302);
    }

    public function testEditTelegramChannel(): void
    {
        $testUser = $this->userRepository->findOneByUsername('test1@test.com');
        $this->client->loginUser($testUser);

        $channel = $testUser->getNotifierChannels()->first();

        $crawler = $this->client->request('GET', '/notifier/edit-channel/' . $channel->getId());
        $form = $crawler->filter('#notifier_telegram_channel_submit')->form();

        $this->client->submit($form, [
            'notifier_telegram_channel[name]' => 'telegram_test2',
            'notifier_telegram_channel[apiToken]' => '653',
            'notifier_telegram_channel[chatId]' => '321',
        ]);

        $updatedChannel = $this->userRepository->findOneByUsername('test1@test.com')->getNotifierChannels()->first();

        $this->assertNotEquals($channel->getName(), $updatedChannel->getName());
        $this->assertNotEquals($channel->getOptions()['apiToken'], $updatedChannel->getOptions()['apiToken']);
        $this->assertNotEquals($channel->getOptions()['chatId'], $updatedChannel->getOptions()['chatId']);
        $this->assertResponseIsSuccessful();
    }

    public function testDeleteTelegramChannel(): void
    {
        $testUser = $this->userRepository->findOneByUsername('test1@test.com');
        $this->client->loginUser($testUser);

        $crawler = $this->client->request('GET', '/notifier/delete-channel/' . $testUser->getNotifierChannels()->first()->getId());
        $form = $crawler->filter('#notifier_delete_channel_submit')->form();

        $this->client->submit($form);

        $this->assertEquals(0, $this->userRepository->findOneByUsername('test1@test.com')->getNotifierChannels()->count());
        $this->assertResponseStatusCodeSame(302);
    }

    public function testTestTelegramChannel(): void
    {
        $testUser = $this->userRepository->findOneByUsername('test1@test.com');
        $this->client->loginUser($testUser);

        $crawler = $this->client->request('GET', '/notifier/test-channel/' . $testUser->getNotifierChannels()->first()->getId());
        $form = $crawler->filter('#notifier_test_channel_submit')->form();

        $crawler = $this->client->submit($form);

        $alert = $crawler->filter('div.alert-success');

        $this->assertNotNull($alert);
        $this->assertEquals(1, $alert->count());
        $this->assertEquals('Notification has been sent', $alert->text());
        $this->assertResponseIsSuccessful();
    }
}
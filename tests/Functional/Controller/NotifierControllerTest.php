<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\NotifierChannel;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NotifierControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private ?UserRepository $userRepository;

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
        $testUser = $this->getUser();
        $this->client->loginUser($testUser);

        $this->client->request('GET', '/notifier/add-channel');

        $this->assertResponseIsSuccessful();
    }

    public function testAddTelegramChannel(): void
    {
        $testUser = $this->getUser();
        $this->client->loginUser($testUser);
        $channelCount = $testUser->getNotifierChannels()->count();

        $crawler = $this->client->request('GET', '/notifier/add-channel/0');
        $form = $crawler->filter('#notifier_telegram_channel_submit')->form();

        $this->client->submit($form, [
            'notifier_telegram_channel[name]' => 'telegram_test2',
            'notifier_telegram_channel[apiToken]' => '653',
            'notifier_telegram_channel[chatId]' => '321',
        ]);

        $this->assertInstanceOf(UserRepository::class, $this->userRepository);
        $updatedUser = $this->userRepository->findOneByUsername('test1@test.com');
        $this->assertInstanceOf(User::class, $updatedUser);

        $this->assertEquals($channelCount + 1, $updatedUser->getNotifierChannels()->count());
        $this->assertResponseStatusCodeSame(302);
    }

    public function testEditTelegramChannel(): void
    {
        $testUser = $this->getUser();
        $this->client->loginUser($testUser);

        $channel = $testUser->getNotifierChannels()->first();
        $this->assertInstanceOf(NotifierChannel::class, $channel);

        $crawler = $this->client->request('GET', '/notifier/edit-channel/' . $channel->getId());
        $form = $crawler->filter('#notifier_telegram_channel_submit')->form();

        $this->client->submit($form, [
            'notifier_telegram_channel[name]' => 'telegram_test2',
            'notifier_telegram_channel[apiToken]' => '653',
            'notifier_telegram_channel[chatId]' => '321',
        ]);

        $this->assertInstanceOf(UserRepository::class, $this->userRepository);
        $user = $this->userRepository->findOneByUsername('test1@test.com');
        $this->assertInstanceOf(User::class, $user);
        $updatedChannel = $user->getNotifierChannels()->first();
        $this->assertInstanceOf(NotifierChannel::class, $updatedChannel);

        $this->assertNotEquals($channel->getName(), $updatedChannel->getName());
        $this->assertNotEquals($channel->getOptions()['apiToken'], $updatedChannel->getOptions()['apiToken']);
        $this->assertNotEquals($channel->getOptions()['chatId'], $updatedChannel->getOptions()['chatId']);
        $this->assertResponseIsSuccessful();
    }

    public function testDeleteTelegramChannel(): void
    {
        $testUser = $this->getUser();
        $this->client->loginUser($testUser);

        $crawler = $this->client->request('GET', '/notifier/delete-channel/' . $testUser->getNotifierChannels()->first()->getId());
        $form = $crawler->filter('#notifier_delete_channel_submit')->form();

        $this->client->submit($form);
        $this->assertInstanceOf(UserRepository::class, $this->userRepository);
        $this->assertEquals(0, $this->userRepository->findOneByUsername('test1@test.com')->getNotifierChannels()->count());
        $this->assertResponseStatusCodeSame(302);
    }

    public function testTestTelegramChannel(): void
    {
        $testUser = $this->getUser();
        $this->client->loginUser($testUser);

        $crawler = $this->client->request('GET', '/notifier/test-channel/' . $testUser->getNotifierChannels()->first()?->getId());
        $form = $crawler->filter('#notifier_test_channel_submit')->form();

        $crawler = $this->client->submit($form);

        $alertSuccess = $crawler->filter('div.test-channel-success');
        $alertError = $crawler->filter('div.test-channel-error');

        $this->assertNotNull($alertSuccess);
        $this->assertEquals(1, $alertSuccess->count());
        $this->assertEquals(0, $alertError->count());
        $this->assertEquals('Notification has been sent', $alertSuccess->text());
        $this->assertResponseIsSuccessful();
    }

    public function testAddDiscordChannel(): void
    {
        $testUser = $this->getUser();
        $this->client->loginUser($testUser);
        $channelCount = $testUser->getNotifierChannels()->count();

        $crawler = $this->client->request('GET', '/notifier/add-channel/1');
        $form = $crawler->filter('#notifier_discord_channel_submit')->form();

        $this->client->submit($form, [
            'notifier_discord_channel[name]' => 'discord_test',
            'notifier_discord_channel[webhook]' => 'https://discord.com/api/webhooks/105/8w8',
        ]);

        $this->assertInstanceOf(UserRepository::class, $this->userRepository);
        $updatedUser = $this->userRepository->findOneByUsername('test1@test.com');
        $this->assertInstanceOf(User::class, $updatedUser);

        $this->assertEquals($channelCount + 1, $updatedUser->getNotifierChannels()->count());
        $this->assertResponseStatusCodeSame(302);
    }

    private function getUser(string $username = 'test1@test.com'): User
    {
        $this->assertInstanceOf(UserRepository::class, $this->userRepository);
        $testUser = $this->userRepository->findOneByUsername($username);
        $this->assertInstanceOf(User::class, $testUser);
        return $testUser;
    }
}

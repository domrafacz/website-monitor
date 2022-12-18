<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler\Notifier;

use App\Message\Notifier\DiscordMessage;
use App\MessageHandler\Notifier\DiscordMessageHandler;
use App\Service\Notifier\Channels\Discord;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class DiscordMessageHandlerTest extends TestCase
{
    public function testMessageHandling(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => 204]);
        $client = new MockHttpClient($mockResponse);
        $discordChannel = new Discord($client);
        $message = new DiscordMessage('test_subject', 'test_message', ['webhook' => 'https://discord.com/api/webhooks/xxx/xxx']);

        $messageHandler = new DiscordMessageHandler($discordChannel);
        $messageHandler($message);

        $this->assertEquals(
            'https://discord.com/api/webhooks/xxx/xxx',
            $mockResponse->getRequestUrl(),
        );
    }
}

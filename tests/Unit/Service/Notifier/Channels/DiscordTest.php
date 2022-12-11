<?php
declare(strict_types=1);

namespace App\Tests\Unit\Service\Notifier\Channels;

use App\Service\Notifier\Channels\Discord;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use InvalidArgumentException;

class DiscordTest extends TestCase
{
    public function testSendSuccess(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => 204]);
        $client = new MockHttpClient($mockResponse);
        $telegramChannel = new Discord($client);

        $this->assertTrue($telegramChannel->send(
            'test_subject',
            'test_message',
            ['webhook' => 'https://discord.com/api/webhooks/xxx/xxx'],
        ));

        $this->assertEquals(
            'https://discord.com/api/webhooks/xxx/xxx',
            $mockResponse->getRequestUrl(),
        );
    }

    public function testSendUnexpectedHttpCode(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => 403]);
        $client = new MockHttpClient($mockResponse);
        $telegramChannel = new Discord($client);

        $this->expectException(UnrecoverableMessageHandlingException::class);

        $telegramChannel->send(
            'test_subject',
            'test_message',
            ['webhook' => 'https://discord.com/api/webhooks/xxx/xxx'],
        );
    }

    public function testSendTransportException(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => 403, 'timeout' => 15.0]);
        $mockResponse->cancel();
        $client = new MockHttpClient($mockResponse);
        $telegramChannel = new Discord($client);

        $this->expectException(UnrecoverableMessageHandlingException::class);

        $telegramChannel->send(
            'test_subject',
            'test_message',
            ['webhook' => 'https://discord.com/api/webhooks/xxx/xxx'],
        );
    }

    public function testLackOfWebhook(): void
    {
        $client = new MockHttpClient();
        $telegramChannel = new Discord($client);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Webhook is not set');

        $telegramChannel->send(
            'test_subject',
            'test_message',
        );
    }

    public function testInvalidWebhook(): void
    {
        $client = new MockHttpClient();
        $telegramChannel = new Discord($client);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Webhook url is invalid');

        $telegramChannel->send(
            'test_subject',
            'test_message',
            ['webhook' => 'https://test.com'],
        );
    }
}
<?php

namespace App\Tests\Unit\Service\Notifier\Channels;

use App\Service\Notifier\Channels\Telegram;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use InvalidArgumentException;

class TelegramTest extends TestCase
{
    public function testSendSuccess(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => 200]);
        $client = new MockHttpClient($mockResponse);
        $telegramChannel = new Telegram($client);

        $this->assertTrue($telegramChannel->send(
            'test_subject',
            'test_message',
            ['apiToken' => '123', 'chatId' => '456'],
        ));

        $this->assertEquals(
            'https://api.telegram.org/bot123/sendMessage?chat_id=456&text=test_subject%0A%0Atest_message',
            $mockResponse->getRequestUrl(),
        );
    }

    public function testSendUnexpectedHttpCode(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => 403]);
        $client = new MockHttpClient($mockResponse);
        $telegramChannel = new Telegram($client);

        $this->expectException(UnrecoverableMessageHandlingException::class);

        $telegramChannel->send(
            'test_subject',
            'test_message',
            ['apiToken' => '123', 'chatId' => '456'],
        );
    }

    public function testSendTransportException(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => 403, 'timeout' => 15.0]);
        $mockResponse->cancel();
        $client = new MockHttpClient($mockResponse);
        $telegramChannel = new Telegram($client);

        $this->expectException(UnrecoverableMessageHandlingException::class);

        $telegramChannel->send(
            'test_subject',
            'test_message',
            ['apiToken' => '123', 'chatId' => '456'],
        );
    }

    public function testLackOfApiToken(): void
    {
        $client = new MockHttpClient();
        $telegramChannel = new Telegram($client);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Telegram apiToken is not set');

        $telegramChannel->send(
            'test_subject',
            'test_message',
            ['chatId' => '456'],
        );
    }

    public function testLackOfChatId(): void
    {
        $client = new MockHttpClient();
        $telegramChannel = new Telegram($client);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Telegram chatId is not set');

        $telegramChannel->send(
            'test_subject',
            'test_message',
            ['apiToken' => '123'],
        );
    }
}
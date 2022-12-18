<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler\Notifier;

use App\Message\Notifier\TelegramMessage;
use App\Service\Notifier\Channels\Telegram;
use App\MessageHandler\Notifier\TelegramMessageHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class TelegramMessageHandlerTest extends TestCase
{
    public function testMessageHandling(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => 200]);
        $client = new MockHttpClient($mockResponse);
        $telegramChannel = new Telegram($client);
        $message = new TelegramMessage('test_subject', 'test_message', ['apiToken' => '123', 'chatId' => '456']);

        $messageHandler = new TelegramMessageHandler($telegramChannel);
        $messageHandler($message);

        $this->assertEquals(
            'https://api.telegram.org/bot123/sendMessage?chat_id=456&text=test_subject%0A%0Atest_message',
            $mockResponse->getRequestUrl(),
        );
    }
}

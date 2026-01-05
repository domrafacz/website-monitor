<?php

declare(strict_types=1);

namespace App\MessageHandler\Notifier;

use App\Message\Notifier\TelegramMessage;
use App\Service\Notifier\Channels\Telegram;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class TelegramMessageHandler
{
    public function __construct(
        private Telegram $telegramChannel,
    ) {
    }

    public function __invoke(TelegramMessage $message): void
    {
        $this->telegramChannel->send($message->subject, $message->message, $message->options);
    }
}

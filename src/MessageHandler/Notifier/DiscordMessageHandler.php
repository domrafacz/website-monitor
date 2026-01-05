<?php

declare(strict_types=1);

namespace App\MessageHandler\Notifier;

use App\Message\Notifier\DiscordMessage;
use App\Service\Notifier\Channels\Discord;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DiscordMessageHandler
{
    public function __construct(
        private Discord $discordChannel,
    ) {
    }

    public function __invoke(DiscordMessage $message): void
    {
        $this->discordChannel->send($message->subject, $message->message, $message->options);
    }
}

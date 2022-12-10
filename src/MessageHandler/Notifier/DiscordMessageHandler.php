<?php
declare(strict_types=1);

namespace App\MessageHandler\Notifier;

use App\Message\Notifier\DiscordMessage;
use App\Service\Notifier\Channels\Discord;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class DiscordMessageHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly Discord $discordChannel,
    ) {}

    public function __invoke(DiscordMessage $message): void
    {
        $this->discordChannel->send($message->subject, $message->message, $message->options);
    }
}

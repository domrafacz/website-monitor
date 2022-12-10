<?php
declare(strict_types=1);

namespace App\Message\Notifier;

use App\Message\Contracts\NotifierMessageInterface;

final class DiscordMessage implements NotifierMessageInterface
{
    public function __construct(
        public string $subject,
        public string $message,
        /** @var array<string, string> $options */
        public array $options,
    ) {}
}
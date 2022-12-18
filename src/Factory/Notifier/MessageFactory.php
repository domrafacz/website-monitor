<?php

declare(strict_types=1);

namespace App\Factory\Notifier;

use App\Message\Contracts\NotifierMessageInterface;
use App\Entity\NotifierChannel;

class MessageFactory
{
    /** @param array<string, string> $options */
    public function create(int $channel, string $subject = '', string $message = '', array $options = []): NotifierMessageInterface
    {
        return new (NotifierChannel::CHANNELS[$channel]['message'])($subject, $message, $options);
    }
}

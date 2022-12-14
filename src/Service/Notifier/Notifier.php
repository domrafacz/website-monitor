<?php

declare(strict_types=1);

namespace App\Service\Notifier;

use App\Factory\Notifier\MessageFactory;
use Symfony\Component\Messenger\MessageBusInterface;

class Notifier
{
    public function __construct(
        private readonly MessageFactory $messageFactory,
        private readonly MessageBusInterface $bus,
    ) {
    }

    /** @param null|array<string, string> $options */
    public function sendNotification(int $type, string $subject, string $message, ?array $options = null): bool
    {
        try {
            $notifierMessage = $this->messageFactory->create($type, $subject, $message, $options ?? []);
            $this->bus->dispatch($notifierMessage);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}

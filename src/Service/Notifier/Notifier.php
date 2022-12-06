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
    ) {}

    public function sendNotification(int $type, string $subject, string $message, ?array $options = null): void
    {
        $notifierMessage = $this->messageFactory->create(0, $subject, $message, $options);
        $this->bus->dispatch($notifierMessage);
    }
}
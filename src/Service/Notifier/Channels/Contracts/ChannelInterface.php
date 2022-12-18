<?php

declare(strict_types=1);

namespace App\Service\Notifier\Channels\Contracts;

interface ChannelInterface
{
    /** @param array<string, string> $options */
    public function send(string $subject = '', string $message = '', array $options = []): bool;
}

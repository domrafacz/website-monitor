<?php

declare(strict_types=1);

namespace App\Service\Notifier\Channels\Contracts;

interface ChannelInterface
{
    public const ID = 0;
    public const NAME = '';
    public const MESSAGE_CLASS = '';
    public const FORM_TYPE_CLASS = '';

    /** @param array<string, string> $options */
    public function send(string $subject = '', string $message = '', array $options = []): bool;
}

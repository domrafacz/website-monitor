<?php

declare(strict_types=1);

namespace App\Service\Notifier\Channels;

use App\Form\NotifierTelegramChannelType;
use App\Message\Notifier\TelegramMessage;
use App\Service\Notifier\Channels\Contracts\ChannelInterface;
use InvalidArgumentException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Telegram implements ChannelInterface
{
    public const ID = 0;
    public const NAME = 'Telegram';
    public const MESSAGE_CLASS = TelegramMessage::class;
    public const FORM_TYPE_CLASS = NotifierTelegramChannelType::class;

    public function __construct(
        private readonly HttpClientInterface $client,
    ) {
    }

    /** @param array<string, string> $options */
    private function validateOptions(array $options): void
    {
        if (!isset($options['apiToken'])) {
            throw new InvalidArgumentException('Telegram apiToken is not set');
        }

        if (!isset($options['chatId'])) {
            throw new InvalidArgumentException('Telegram chatId is not set');
        }
    }

    public function send(string $subject = '', string $message = '', array $options = []): bool
    {
        $this->validateOptions($options);

        $message = $subject . "\n\n" . $message;

        try {
            $response = $this->client->request(
                'GET',
                sprintf(
                    'https://api.telegram.org/bot%s/sendMessage?chat_id=%s&text=%s',
                    $options['apiToken'],
                    $options['chatId'],
                    urlencode($message)
                ),
                ['timeout' => 10.0],
            );

            if ($response->getStatusCode() !== 200) {
                throw new UnrecoverableMessageHandlingException();
            }
        } catch (TransportExceptionInterface $e) {
            throw new UnrecoverableMessageHandlingException();
        }

        return true;
    }
}

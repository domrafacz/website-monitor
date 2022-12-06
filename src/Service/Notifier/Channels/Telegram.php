<?php
declare(strict_types=1);

namespace App\Service\Notifier\Channels;

use App\Service\Notifier\Channels\Contracts\ChannelInterface;
use InvalidArgumentException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Telegram implements ChannelInterface
{
    public function __construct(
        private readonly HttpClientInterface $client,
    ) {}

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

    public function send(string $subject = '', string $message = '', array $options = []): void
    {
        $this->validateOptions($options);

        $message = $subject . "\n\n" . $message;

        try {
            $response = $this->client->request(
                'GET',
                sprintf('https://api.telegram.org/bot%s/sendMessage?chat_id=%s&text=%s',
                    $options['apiToken'],
                    $options['chatId'],
                    urlencode($message)
                ),
            );

            if ($response->getStatusCode() !== 200) {
                throw new UnrecoverableMessageHandlingException();
            }
        } catch (TransportExceptionInterface $e) {
        }
    }

}
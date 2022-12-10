<?php
declare(strict_types=1);

namespace App\Service\Notifier\Channels;

use App\Service\Notifier\Channels\Contracts\ChannelInterface;
use InvalidArgumentException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Discord implements ChannelInterface
{
    public function __construct(
        private readonly HttpClientInterface $client,
    ) {}

    /** @param array<string, string> $options */
    private function validateOptions(array $options): void
    {
        if (!isset($options['webhook'])) {
            throw new InvalidArgumentException('Webhook is not set');
        }

        if (!str_starts_with($options['webhook'], 'https://discord.com/api/webhooks/')) {
            throw new InvalidArgumentException('Webhook url is invalid');
        }
    }

    public function send(string $subject = '', string $message = '', array $options = []): bool
    {
        $this->validateOptions($options);

        $message = $subject . "\n\n" . $message;

        try {
            $response = $this->client->request('POST', $options['webhook'], [
                'timeout' => 10.0,
                'json' => ['content' => $message],
            ]);

            if ($response->getStatusCode() !== 204) {
                throw new UnrecoverableMessageHandlingException();
            }
        } catch (TransportExceptionInterface $e) {
            throw new UnrecoverableMessageHandlingException();
        }

        return true;
    }
}
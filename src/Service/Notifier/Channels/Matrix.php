<?php

declare(strict_types=1);

namespace App\Service\Notifier\Channels;

use App\Form\NotifierMatrixChannelType;
use App\Form\NotifierTelegramChannelType;
use App\Message\Notifier\MatrixMessage;
use App\Message\Notifier\TelegramMessage;
use App\Service\Notifier\Channels\Contracts\ChannelInterface;
use InvalidArgumentException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Matrix implements ChannelInterface
{
    public const ID = 2;
    public const NAME = 'Matrix';
    public const MESSAGE_CLASS = MatrixMessage::class;
    public const FORM_TYPE_CLASS = NotifierMatrixChannelType::class;

    public function __construct(
        private readonly HttpClientInterface $client,
    ) {
    }

    /** @param array<string, string> $options */
    private function validateOptions(array $options): void
    {
        if (!isset($options['serverUrl'])) {
            throw new InvalidArgumentException('Matrix serverUrl is not set');
        }

        if (!isset($options['accessToken'])) {
            throw new InvalidArgumentException('Matrix accessToken is not set');
        }

        if (!isset($options['roomId'])) {
            throw new InvalidArgumentException('Matrix roomId is not set');
        }
    }

    public function send(string $subject = '', string $message = '', array $options = []): bool
    {
        $this->validateOptions($options);

        $message = $subject . "\n\n" . $message;

        $serverUrl = $options['serverUrl'];
        $accessToken = $options['accessToken'];
        $roomId = urlencode($options['roomId']);
        $url = "$serverUrl/_matrix/client/v3/rooms/$roomId/send/m.room.message?access_token=$accessToken";

        try {
            $payload = [
                'msgtype' => 'm.text',
                'body' => $message
            ];

            $response = $this->client->request('POST', $url, [
                'timeout' => 10.0,
                'json' => $payload,
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new UnrecoverableMessageHandlingException();
            }
        } catch (TransportExceptionInterface $e) {
            throw new UnrecoverableMessageHandlingException();
        }

        return true;
    }
}

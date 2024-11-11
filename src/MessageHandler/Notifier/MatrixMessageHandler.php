<?php

declare(strict_types=1);

namespace App\MessageHandler\Notifier;

use App\Message\Notifier\MatrixMessage;
use App\Service\Notifier\Channels\Matrix;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class MatrixMessageHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly Matrix $matrixChannel,
    ) {
    }

    public function __invoke(MatrixMessage $message): void
    {
        $this->matrixChannel->send($message->subject, $message->message, $message->options);
    }
}

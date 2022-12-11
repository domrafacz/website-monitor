<?php
declare(strict_types=1);

namespace App\Tests\Unit\Service\Notifier;

use App\Factory\Notifier\MessageFactory;
use App\Service\Notifier\Notifier;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBus;

class NotifierTest extends TestCase
{
    public function testSendNotificationSuccess(): void
    {
        $bus = new class extends MessageBus {
            public function dispatch(object $message, array $stamps = []):  Envelope {
                return new Envelope($message);
            }
        };

        $factory = new MessageFactory();
        $notifier = new Notifier($factory, $bus);

        $result = $notifier->sendNotification(0, 'subject', 'message');

        $this->assertTrue($result);
    }

    public function testSendNotificationFailure(): void
    {
        $bus = new class extends MessageBus {
            public function dispatch(object $message, array $stamps = []):  Envelope {
                throw new UnrecoverableMessageHandlingException();
            }
        };

        $factory = new MessageFactory();
        $notifier = new Notifier($factory, $bus);

        $result = $notifier->sendNotification(0, 'subject', 'message');

        $this->assertFalse($result);
    }
}
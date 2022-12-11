<?php
declare(strict_types=1);

namespace App\Tests\Integration\Service\Notifier;

use App\Service\Notifier\Notifier;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\MessageBusInterface;

class NotifierTest  extends KernelTestCase
{
    public function testSendNotification(): void
    {
        self::bootKernel();
        $notifier = $this->getContainer()->get(Notifier::class);
        $notifier->sendNotification(0, 'telegram_subject1', 'telegram_message1', ['apiToken' => '123', 'chatId' => '456']);
        $notifier->sendNotification(0, 'telegram_subject2', 'telegram_message2', ['apiToken' => '321', 'chatId' => '654']);

        $this->getContainer()->get(MessageBusInterface::class);

        $transport = $this->getContainer()->get('messenger.transport.sync');
        $this->assertCount(2, $transport->getSent());
    }
}
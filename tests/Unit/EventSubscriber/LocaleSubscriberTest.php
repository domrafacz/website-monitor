<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventSubscriber;

use App\EventSubscriber\LocaleSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @covers \App\EventSubscriber\LocaleSubscriber
 */
class LocaleSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $events = LocaleSubscriber::getSubscribedEvents();
        $this->assertArrayHasKey(KernelEvents::REQUEST, $events);

        $methodName = $events[KernelEvents::REQUEST][0][0];
        $this->assertTrue(method_exists(LocaleSubscriber::class, $methodName));
    }
}

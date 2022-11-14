<?php
declare(strict_types=1);

namespace App\Tests\Unit\EventSubscriber;
use App\EventSubscriber\LocaleSubscriber;
use App\EventSubscriber\UserLocaleSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * @covers \App\EventSubscriber\UserLocaleSubscriber
 */
class UserLocaleSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $events = UserLocaleSubscriber::getSubscribedEvents();
        $this->assertArrayHasKey(SecurityEvents::INTERACTIVE_LOGIN, $events);

        $methodName = $events[SecurityEvents::INTERACTIVE_LOGIN];
        $this->assertTrue(method_exists(UserLocaleSubscriber::class, $methodName));
    }
}
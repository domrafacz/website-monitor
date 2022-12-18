<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\DowntimeLog;
use App\Entity\Website;
use PHPUnit\Framework\TestCase;

class DowntimeLogTest extends TestCase
{
    public function testSetterAndGetter(): void
    {
        $website = new class () extends Website {
            public function getId(): int
            {
                return 1;
            }
        };

        $downtimeLog = new class () extends DowntimeLog {
            public function getId(): int
            {
                return 1;
            }
        };

        $startTime = new \DateTimeImmutable();
        $endTime = $startTime->setTimestamp($startTime->getTimestamp() + 300);

        $this->assertSame(1, $downtimeLog->getId());
        $this->assertNull((new DowntimeLog())->getId());

        $this->assertInstanceOf(DowntimeLog::class, $downtimeLog->setWebsite($website));
        $this->assertSame($website, $downtimeLog->getWebsite());

        $this->assertInstanceOf(DowntimeLog::class, $downtimeLog->setStartTime($startTime));
        $this->assertSame($startTime, $downtimeLog->getStartTime());

        $this->assertInstanceOf(DowntimeLog::class, $downtimeLog->setEndTime($endTime));
        $this->assertSame($endTime, $downtimeLog->getEndTime());

        $this->assertInstanceOf(DowntimeLog::class, $downtimeLog->setInitialError(['timeout']));
        $this->assertSame(['timeout'], $downtimeLog->getInitialError());
    }
}

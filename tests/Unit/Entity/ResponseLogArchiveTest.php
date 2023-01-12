<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\ResponseLogArchive;
use App\Entity\Website;
use PHPUnit\Framework\TestCase;

class ResponseLogArchiveTest extends TestCase
{
    public function testSetterAndGetter(): void
    {
        $time = new \DateTimeImmutable();

        $website = new class () extends Website {
            public function getId(): int
            {
                return 1;
            }
        };

        $responseLogArchive = new class (
            $website,
            $time,
            100
        ) extends ResponseLogArchive {
            public function getId(): int
            {
                return 1;
            }
        };

        $this->assertSame(1, $responseLogArchive->getId());
        $this->assertNull((new ResponseLogArchive(
            $website,
            $time,
            100
        ))->getId());

        $this->assertSame(100, $responseLogArchive->getAverageResponseTime());
        $this->assertInstanceOf(ResponseLogArchive::class, $responseLogArchive->setAverageResponseTime(500));
        $this->assertSame(500, $responseLogArchive->getAverageResponseTime());

        $this->assertSame($time, $responseLogArchive->getDate());
        $this->assertInstanceOf(ResponseLogArchive::class, $responseLogArchive->setDate($time->modify('-1 day')));
        $this->assertEquals($time->modify('-1 day'), $responseLogArchive->getDate());
    }
}

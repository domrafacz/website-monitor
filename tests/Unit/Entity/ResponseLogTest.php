<?php
declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\ResponseLog;
use App\Entity\Website;
use PHPUnit\Framework\TestCase;

class ResponseLogTest extends TestCase
{
    public function testSetterAndGetter(): void
    {
        $website = new class extends Website {

            public function getId(): int {
                return 1;
            }
        };

        $responseLog = new class(
            $website,
            ResponseLog::STATUS_OK,
            new \DateTimeImmutable(),
            500,

        ) extends ResponseLog {

            public function getId(): int {
                return 1;
            }
        };

        $time = new \DateTimeImmutable();

        $this->assertSame(1, $responseLog->getId());
        $this->assertNull((new ResponseLog(
            $website,
            ResponseLog::STATUS_OK,
            new \DateTimeImmutable(),
        600
        ))->getId());

        $this->assertInstanceOf(ResponseLog::class, $responseLog->setStatus(ResponseLog::STATUS_ERROR));
        $this->assertSame(ResponseLog::STATUS_ERROR, $responseLog->getStatus());

        $this->assertInstanceOf(ResponseLog::class, $responseLog->setTime($time));
        $this->assertSame($time, $responseLog->getTime());

        $this->assertInstanceOf(ResponseLog::class, $responseLog->setResponseTime(500));
        $this->assertSame(500, $responseLog->getResponseTime());
    }
}
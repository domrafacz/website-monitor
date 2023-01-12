<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\ResponseLog;
use App\Entity\Website;
use App\Repository\ResponseLogArchiveRepository;
use App\Repository\ResponseLogRepository;
use App\Service\ResponseLogArchiver;
use App\Tests\Unit\Traits\WebsiteTrait;
use PHPUnit\Framework\TestCase;

class ResponseLogArchiverTest extends TestCase
{
    use WebsiteTrait;

    private Website $website;
    private ResponseLogRepository $responseLogRepository;
    private ResponseLogArchiveRepository $responseLogArchiveRepository;
    private \DateTimeImmutable $datetime;

    protected function setUp(): void
    {
        $this->website = $this->createWebsite(10);
        $this->responseLogRepository = $this->createMock(ResponseLogRepository::class);
        $this->responseLogArchiveRepository = $this->createMock(ResponseLogArchiveRepository::class);
        $this->datetime = new \DateTimeImmutable();
    }

    public function testNoLogsToArchive(): void
    {
        $this->responseLogRepository->method('getOldest')->willReturn(null);
        $responseLogArchiver = new ResponseLogArchiver($this->responseLogRepository, $this->responseLogArchiveRepository);

        $this->assertFalse($responseLogArchiver->archive($this->website, new \DateTimeImmutable()));
    }

    public function testNoDueLogsToArchive(): void
    {
        $responseLogArchiver = new ResponseLogArchiver($this->responseLogRepository, $this->responseLogArchiveRepository);
        $responseLog = new ResponseLog($this->website, ResponseLog::STATUS_OK, $this->datetime, 100);
        $this->responseLogRepository->method('getOldest')->willReturn($responseLog);

        $this->assertFalse($responseLogArchiver->archive($this->website, $this->datetime->modify('-50 day')));
    }

    public function testArchiveLogs(): void
    {
        $responseLogArchiver = new ResponseLogArchiver($this->responseLogRepository, $this->responseLogArchiveRepository);
        $responseLog = new ResponseLog($this->website, ResponseLog::STATUS_OK, $this->datetime->modify('-3 day'), 100);
        $this->responseLogRepository->method('getOldest')->willReturn($responseLog);

        $this->responseLogRepository->expects($this->exactly(1))->method('getAverageResponseTimeForOlderThan');
        $this->responseLogRepository->expects($this->exactly(1))->method('deleteOlderThan');
        $this->responseLogArchiveRepository->expects($this->exactly(1))->method('save');
        $this->assertTrue($responseLogArchiver->archive($this->website, $this->datetime->modify('-1 day')));
    }
}

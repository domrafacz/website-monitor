<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\ResponseLogArchive;
use App\Entity\Website;
use App\Repository\ResponseLogArchiveRepository;
use App\Repository\ResponseLogRepository;

class ResponseLogArchiver
{
    public function __construct(
        private readonly ResponseLogRepository $responseLogRepository,
        private readonly ResponseLogArchiveRepository $responseLogArchiveRepository,
    ) {
    }

    /**
     * This function archives the oldest available day, if you want to archive all due logs,
     * you have to call this function in loop until it returns false
     */
    public function archive(Website $website, \DateTimeInterface $dueTime): bool
    {
        $log = $this->responseLogRepository->getOldest($website);

        if ($log === null) {
            return false;
        }

        if ($log->getTime()->getTimestamp() > $dueTime->getTimestamp()) {
            return false;
        }

        $endTime = new \DateTimeImmutable($log->getTime()->format('Y-m-d 23:59:59'));

        $averageResponseTime = $this->responseLogRepository->getAverageResponseTimeForOlderThan($website, $endTime);

        $this->responseLogArchiveRepository->save(new ResponseLogArchive(
            $website,
            $endTime,
            $averageResponseTime
        ), true);

        $this->responseLogRepository->deleteOlderThan($website, $endTime);

        return true;
    }
}

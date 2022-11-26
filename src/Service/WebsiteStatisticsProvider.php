<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\DowntimeLog;
use App\Entity\Website;

class WebsiteStatisticsProvider
{
    public function getDowntimeInSecondsFilterByPeriod(Website $website, \DateTimeImmutable $startTime, \DateTimeImmutable $endTime): int
    {
        $downtimeLogs = $website->getDowntimeLogs();
        $startTime = $startTime->getTimestamp();
        $endTime = $endTime->getTimestamp();
        $downtimeInSeconds = 0;

        $filteredLogs = $downtimeLogs->filter(function(DowntimeLog $log) use ($startTime, $endTime): bool {
            if (
                $log->getEndTime() == null && $log->getStartTime()->getTimestamp() >= $startTime ||
                ($log->getEndTime() != null  && $log->getEndTime()->getTimestamp() > $startTime) && ($log->getEndTime()->getTimestamp() <= $endTime)
            ) {
                return true;
            } else {
                return false;
            }
        });

        foreach ($filteredLogs->getIterator() as $log) {
            if ($log->getEndTime() == null) {
                $downtimeInSeconds += $endTime - $log->getStartTime()->getTimestamp();
            } else {
                $downtimeInSeconds += $log->getEndTime()->getTimestamp() - max(($startTime), $log->getStartTime()->getTimestamp());
            }
        }

        return $downtimeInSeconds;
    }

    public function getUptime24H(Website $website): float
    {
        $startTime = new \DateTimeImmutable();
        $startTime = $startTime->setTimestamp($startTime->getTimestamp() - 86400);

        $downtimeInSeconds = $this->getDowntimeInSecondsFilterByPeriod($website, $startTime, new \DateTimeImmutable());

        return round(((86400 - $downtimeInSeconds) * 100) / 86400, 2);
    }

    public function getUptime30D(Website $website): float
    {
        $startTime = new \DateTimeImmutable();
        $startTime = $startTime->setTimestamp($startTime->getTimestamp() - 2592000);

        $downtimeInSeconds = $this->getDowntimeInSecondsFilterByPeriod($website, $startTime, new \DateTimeImmutable());

        return round(((2592000 - $downtimeInSeconds) * 100) / 2592000, 2);
    }
}
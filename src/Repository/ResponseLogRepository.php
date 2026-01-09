<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ResponseLog;
use App\Entity\Website;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ResponseLog>
 *
 * @method ResponseLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResponseLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResponseLog[]    findAll()
 * @method ResponseLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResponseLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResponseLog::class);
    }

    public function getAverageResponseTimeFilterByPeriod(Website $website, \DateTimeImmutable $startTime, \DateTimeImmutable $endTime): int
    {
        $query = $this->getEntityManager()->createQuery(
            'SELECT AVG(r.responseTime) AS average
            FROM App\Entity\ResponseLog r
            WHERE r.website = :website
            AND r.time >= :startTime
            AND r.time <= :endTime'
        )->setParameter('website', $website)
            ->setParameter('startTime', $startTime)
            ->setParameter('endTime', $endTime);

        $result = $query->getResult();

        return intval($result[0]['average']);
    }

    public function getAverageResponseTimeForOlderThan(Website $website, \DateTimeImmutable $endTime): int
    {
        $query = $this->getEntityManager()->createQuery(
            'SELECT AVG(r.responseTime) AS average
            FROM App\Entity\ResponseLog r
            WHERE r.website = :website
            AND r.time <= :endTime'
        )->setParameter('website', $website)
            ->setParameter('endTime', $endTime);

        $result = $query->getResult();

        return intval($result[0]['average']);
    }


    public function getOldest(Website $website): ?ResponseLog
    {
        $result = $this->createQueryBuilder('r')
            ->andWhere('r.website = :website')
            ->setParameter('website', $website)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if ($result instanceof ResponseLog) {
            return $result;
        } else {
            return null;
        }
    }

    /**
     * Get aggregated chart data with time-bucketed averages
     * 
     * @return array<int, array{bucket_time: \DateTimeImmutable, avg_response_time: int, uptime_percent: float}>
     */
    public function getAggregatedChartData(
        Website $website,
        \DateTimeImmutable $startTime,
        \DateTimeImmutable $endTime,
        int $intervalSeconds
    ): array {
        $conn = $this->getEntityManager()->getConnection();
        $platform = $conn->getDatabasePlatform()->getName();

        // Use platform-specific SQL for time bucketing
        if ($platform === 'postgresql') {
            $sql = "
                SELECT 
                    to_timestamp(FLOOR(EXTRACT(EPOCH FROM r.time) / :interval) * :interval) AS bucket_time,
                    AVG(r.response_time) AS avg_response_time,
                    (SUM(CASE WHEN r.status = 1 THEN 1 ELSE 0 END)::float / COUNT(*)) * 100 AS uptime_percent
                FROM response_log r
                WHERE r.website_id = :websiteId
                AND r.time >= :startTime
                AND r.time <= :endTime
                GROUP BY FLOOR(EXTRACT(EPOCH FROM r.time) / :interval)
                ORDER BY bucket_time ASC
            ";
        } elseif ($platform === 'mysql') {
            $sql = "
                SELECT 
                    FROM_UNIXTIME(FLOOR(UNIX_TIMESTAMP(r.time) / :interval) * :interval) AS bucket_time,
                    AVG(r.response_time) AS avg_response_time,
                    (SUM(CASE WHEN r.status = 1 THEN 1 ELSE 0 END) / COUNT(*)) * 100 AS uptime_percent
                FROM response_log r
                WHERE r.website_id = :websiteId
                AND r.time >= :startTime
                AND r.time <= :endTime
                GROUP BY FLOOR(UNIX_TIMESTAMP(r.time) / :interval)
                ORDER BY bucket_time ASC
            ";
        } else {
            // SQLite fallback
            $sql = "
                SELECT 
                    datetime((strftime('%s', r.time) / :interval) * :interval, 'unixepoch') AS bucket_time,
                    AVG(r.response_time) AS avg_response_time,
                    (CAST(SUM(CASE WHEN r.status = 1 THEN 1 ELSE 0 END) AS REAL) / COUNT(*)) * 100 AS uptime_percent
                FROM response_log r
                WHERE r.website_id = :websiteId
                AND r.time >= :startTime
                AND r.time <= :endTime
                GROUP BY (strftime('%s', r.time) / :interval)
                ORDER BY bucket_time ASC
            ";
        }

        $result = $conn->executeQuery($sql, [
            'websiteId' => $website->getId(),
            'startTime' => $startTime->format('Y-m-d H:i:s'),
            'endTime' => $endTime->format('Y-m-d H:i:s'),
            'interval' => $intervalSeconds,
        ]);

        $data = [];
        foreach ($result->fetchAllAssociative() as $row) {
            $bucketTime = is_string($row['bucket_time']) ? $row['bucket_time'] : '';
            $avgResponseTime = is_numeric($row['avg_response_time'] ?? null) ? intval($row['avg_response_time']) : 0;
            $uptimePercent = is_numeric($row['uptime_percent'] ?? null) ? floatval($row['uptime_percent']) : 100.0;

            $data[] = [
                'bucket_time' => new \DateTimeImmutable($bucketTime),
                'avg_response_time' => $avgResponseTime,
                'uptime_percent' => $uptimePercent,
            ];
        }

        return $data;
    }

    public function deleteOlderThan(Website $website, \DateTimeImmutable $endTime): void
    {
        $this->getEntityManager()->createQuery(
            'DELETE FROM App\Entity\ResponseLog r
            WHERE r.website = :website
            AND r.time <= :endTime'
        )
            ->setParameter('website', $website)
            ->setParameter('endTime', $endTime)
            ->execute();
    }
}

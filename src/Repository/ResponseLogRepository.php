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

    public function deleteOlderThan(Website $website, \DateTimeImmutable $endTime): void
    {
        $query = $this->getEntityManager()->createQuery(
            'DELETE FROM App\Entity\ResponseLog r
            WHERE r.website = :website
            AND r.time <= :endTime'
        )
            ->setParameter('website', $website)
            ->setParameter('endTime', $endTime)
            ->execute();
    }

//    /**
//     * @return ResponseLog[] Returns an array of ResponseLog objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ResponseLog
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

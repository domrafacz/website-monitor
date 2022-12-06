<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\NotifierChannel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NotifierChannel>
 *
 * @method NotifierChannel|null find($id, $lockMode = null, $lockVersion = null)
 * @method NotifierChannel|null findOneBy(array $criteria, array $orderBy = null)
 * @method NotifierChannel[]    findAll()
 * @method NotifierChannel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotifierChannelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotifierChannel::class);
    }

    public function save(NotifierChannel $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(NotifierChannel $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return NotifierChannel[] Returns an array of NotifierChannel objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('n.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?NotifierChannel
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

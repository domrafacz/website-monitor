<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ResponseLogArchive;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ResponseLogArchive>
 *
 * @method ResponseLogArchive|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResponseLogArchive|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResponseLogArchive[]    findAll()
 * @method ResponseLogArchive[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResponseLogArchiveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResponseLogArchive::class);
    }

    public function save(ResponseLogArchive $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}

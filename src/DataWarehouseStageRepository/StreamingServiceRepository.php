<?php

namespace App\DataWarehouseStageRepository;

use App\DataWarehouseStage\StreamingService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method StreamingService|null find($id, $lockMode = null, $lockVersion = null)
 * @method StreamingService|null findOneBy(array $criteria, array $orderBy = null)
 * @method StreamingService[]    findAll()
 * @method StreamingService[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StreamingServiceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, StreamingService::class);
    }

    public function findByCanonicalName(string $canonicalName): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.canonicalName = :val')
            ->setParameter('val', $canonicalName)
            ->getQuery()
            ->getResult();
    }

    public function existByCanonicalName(string $canonicalName): bool
    {
        return \count($this->createQueryBuilder('a')
                ->andWhere('a.canonicalName = :val')
                ->setParameter('val', $canonicalName)
                ->getQuery()
                ->getResult()) > 0;
    }

//    /**
//     * @return Artist[] Returns an array of Artist objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Artist
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

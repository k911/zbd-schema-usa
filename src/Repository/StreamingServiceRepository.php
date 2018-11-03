<?php

namespace App\Repository;

use App\Entity\StreamingService;
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

//    /**
//     * @return StreamingService[] Returns an array of StreamingService objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?StreamingService
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

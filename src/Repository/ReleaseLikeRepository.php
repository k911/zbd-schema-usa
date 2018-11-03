<?php

namespace App\Repository;

use App\Entity\ReleaseLike;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ReleaseLike|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReleaseLike|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReleaseLike[]    findAll()
 * @method ReleaseLike[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReleaseLikeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ReleaseLike::class);
    }

//    /**
//     * @return ReleaseLike[] Returns an array of ReleaseLike objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ReleaseLike
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

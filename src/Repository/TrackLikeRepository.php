<?php

namespace App\Repository;

use App\Entity\TrackLike;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TrackLike|null find($id, $lockMode = null, $lockVersion = null)
 * @method TrackLike|null findOneBy(array $criteria, array $orderBy = null)
 * @method TrackLike[]    findAll()
 * @method TrackLike[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrackLikeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TrackLike::class);
    }

//    /**
//     * @return TrackLike[] Returns an array of TrackLike objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TrackLike
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

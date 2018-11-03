<?php

namespace App\Repository;

use App\Entity\TrackStream;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TrackStream|null find($id, $lockMode = null, $lockVersion = null)
 * @method TrackStream|null findOneBy(array $criteria, array $orderBy = null)
 * @method TrackStream[]    findAll()
 * @method TrackStream[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrackStreamRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TrackStream::class);
    }

//    /**
//     * @return TrackStream[] Returns an array of TrackStream objects
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
    public function findOneBySomeField($value): ?TrackStream
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

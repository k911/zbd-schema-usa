<?php

namespace App\Repository;

use App\Entity\MusicLabel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MusicLabel|null find($id, $lockMode = null, $lockVersion = null)
 * @method MusicLabel|null findOneBy(array $criteria, array $orderBy = null)
 * @method MusicLabel[]    findAll()
 * @method MusicLabel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MusicLabelRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MusicLabel::class);
    }

//    /**
//     * @return MusicLabel[] Returns an array of MusicLabel objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MusicLabel
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

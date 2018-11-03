<?php

namespace App\Repository;

use App\Entity\MusicLabelArtistContract;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MusicLabelArtistContract|null find($id, $lockMode = null, $lockVersion = null)
 * @method MusicLabelArtistContract|null findOneBy(array $criteria, array $orderBy = null)
 * @method MusicLabelArtistContract[]    findAll()
 * @method MusicLabelArtistContract[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MusicLabelArtistContractRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MusicLabelArtistContract::class);
    }

//    /**
//     * @return MusicLabelArtistContract[] Returns an array of MusicLabelArtistContract objects
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
    public function findOneBySomeField($value): ?MusicLabelArtistContract
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

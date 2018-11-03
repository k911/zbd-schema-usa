<?php

namespace App\Repository;

use App\Entity\MusicLabelStreamingServiceContract;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MusicLabelStreamingServiceContract|null find($id, $lockMode = null, $lockVersion = null)
 * @method MusicLabelStreamingServiceContract|null findOneBy(array $criteria, array $orderBy = null)
 * @method MusicLabelStreamingServiceContract[]    findAll()
 * @method MusicLabelStreamingServiceContract[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MusicLabelStreamingServiceContractRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MusicLabelStreamingServiceContract::class);
    }

//    /**
//     * @return MusicLabelStreamingServiceContract[] Returns an array of MusicLabelStreamingServiceContract objects
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
    public function findOneBySomeField($value): ?MusicLabelStreamingServiceContract
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

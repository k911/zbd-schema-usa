<?php

namespace App\DataWarehouseStageRepository;

use App\DataWarehouseStage\Artist;
use App\DataWarehouseStage\Release;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Release|null find($id, $lockMode = null, $lockVersion = null)
 * @method Release|null findOneBy(array $criteria, array $orderBy = null)
 * @method Release[]    findAll()
 * @method Release[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReleaseRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Release::class);
    }

    public function findByUpc(int $upc): ?Release
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.upc = :val')
            ->setParameter('val', $upc)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function existByUpc(int $upc): bool
    {
        return \count($this->createQueryBuilder('a')
                ->andWhere('a.upc = :val')
                ->setParameter('val', $upc)
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

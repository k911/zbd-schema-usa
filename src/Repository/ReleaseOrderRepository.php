<?php

namespace App\Repository;

use App\Entity\ReleaseOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ReleaseOrder|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReleaseOrder|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReleaseOrder[]    findAll()
 * @method ReleaseOrder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReleaseOrderRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ReleaseOrder::class);
    }

//    /**
//     * @return ReleaseOrder[] Returns an array of ReleaseOrder objects
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
    public function findOneBySomeField($value): ?ReleaseOrder
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

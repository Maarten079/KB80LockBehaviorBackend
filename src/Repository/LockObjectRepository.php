<?php

namespace App\Repository;

use App\Entity\LockObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LockObject|null find($id, $lockMode = null, $lockVersion = null)
 * @method LockObject|null findOneBy(array $criteria, array $orderBy = null)
 * @method LockObject[]    findAll()
 * @method LockObject[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LockObjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LockObject::class);
    }

    public function getQueryBuilder() {
        return $this->createQueryBuilder('l');
    }

    // /**
    //  * @return LockObject[] Returns an array of LockObject objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LockObject
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

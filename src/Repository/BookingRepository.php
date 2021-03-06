<?php

namespace App\Repository;

use App\Entity\Booking;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 */
class BookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booking::class);
    }


    /**
    *
    */
    public function findLatest()
    {
        return $this->createQueryBuilder('b')
            ->addSelect('a')
            ->innerJoin('b.author', 'a')
            ->where('b.beginAt >= :now')
            ->orderBy('b.beginAt', 'ASC')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     *
     */
    public function findExpired()
    {
        return $this->createQueryBuilder('b')
            ->where('b.beginAt < :now')
            ->where('b.endTime < :now')
            ->andWhere('b.status = :status')
            ->setParameter('now', new \DateTime())
            ->setParameter('status', 'new')
            ->getQuery()
            ->getResult()
            ;
    }


    // /**
    //  * @return Booking[] Returns an array of Booking objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */


    public function findOneBySomeField($value): ?Booking
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

}

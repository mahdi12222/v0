<?php

namespace App\Repository;

use App\Entity\Bloc;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Bloc|null find($id, $lockMode = null, $lockVersion = null)
 * @method Bloc|null findOneBy(array $criteria, array $orderBy = null)
 * @method Bloc[]    findAll()
 * @method Bloc[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BlocRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bloc::class);
    }

    /**
     * @return Bloc[] Returns an array of Bloc objects
     */
    public function findByParent($parent)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.parent = :val')
            ->setParameter('val', $parent)
            ->orderBy('b.ordre', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Bloc[] Returns an array of Bloc objects
     */
    public function findRoot()
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.parent IS NULL')
            ->orderBy('b.ordre', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    /*
    public function findOneBySomeField($value): ?Bloc
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

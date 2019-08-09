<?php

namespace App\Repository;

use App\Entity\ListDepartment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ListDepartment|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListDepartment|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListDepartment[]    findAll()
 * @method ListDepartment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListDepartmentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ListDepartment::class);
    }

    // /**
    //  * @return ListDepartment[] Returns an array of ListDepartment objects
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
    public function findOneBySomeField($value): ?ListDepartment
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

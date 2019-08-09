<?php

namespace App\Repository;

use App\Entity\PlannedMeal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PlannedMeal|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlannedMeal|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlannedMeal[]    findAll()
 * @method PlannedMeal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlannedMealRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PlannedMeal::class);
    }

    // /**
    //  * @return PlannedMeal[] Returns an array of PlannedMeal objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PlannedMeal
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

<?php

namespace App\Repository;

use App\Entity\CustomerObservations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CustomerObservations|null find($id, $lockMode = null, $lockVersion = null)
 * @method CustomerObservations|null findOneBy(array $criteria, array $orderBy = null)
 * @method CustomerObservations[]    findAll()
 * @method CustomerObservations[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerObservationsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CustomerObservations::class);
    }

//    /**
//     * @return CustomerObservations[] Returns an array of CustomerObservations objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CustomerObservations
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

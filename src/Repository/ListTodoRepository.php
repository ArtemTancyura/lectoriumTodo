<?php

namespace App\Repository;

use App\Entity\ListTodo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ListTodo|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListTodo|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListTodo[]    findAll()
 * @method ListTodo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListTodoRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ListTodo::class);
    }

    // /**
    //  * @return ListTodo[] Returns an array of ListTodo objects
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
    public function findOneBySomeField($value): ?ListTodo
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

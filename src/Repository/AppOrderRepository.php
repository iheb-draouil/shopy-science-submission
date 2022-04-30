<?php

namespace App\Repository;

use App\Entity\AppOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AppOrder>
 *
 * @method AppOrder|null find($id, $lockMode = null, $lockVersion = null)
 * @method AppOrder|null findOneBy(array $criteria, array $orderBy = null)
 * @method AppOrder[]    findAll()
 * @method AppOrder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AppOrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppOrder::class);
    }

    public function findHavingCodeOrNumber(string $order_number, string $code)
    {
        $qb = $this->createQueryBuilder('o')
        ->where('o.order_number = :order_number')
        ->orWhere('o.code = :code')
        ->setParameter('order_number', $order_number)
        ->setParameter('code', $code);

        $query = $qb->getQuery();

        return $query->execute();
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(AppOrder $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(AppOrder $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return AppOrder[] Returns an array of AppOrder objects
    //  */
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
    public function findOneBySomeField($value): ?AppOrder
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

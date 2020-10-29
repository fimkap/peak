<?php

namespace App\Repository;

use App\Entity\Call;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Call|null find($id, $lockMode = null, $lockVersion = null)
 * @method Call|null findOneBy(array $criteria, array $orderBy = null)
 * @method Call[]    findAll()
 * @method Call[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CallRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Call::class);
    }

    // /**
    //  * @return Call[] Returns an array of Call objects
    //  */
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
    public function findOneBySomeField($value): ?Call
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getCallStats(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT DISTINCT `customer_id`, count(*) as `total_calls`, sum(`duration`) as `duration` from `call` group by `customer_id`;
        ';
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAll();
    }
}

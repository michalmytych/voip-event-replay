<?php

namespace App\Repository;

use App\Entity\VoipEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VoipEvent>
 */
class VoipEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VoipEvent::class);
    }

//    /**
//     * @return VoipEvent[] Returns an array of VoipEvent objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('v.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?VoipEvent
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Entity\VoipEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Domain\Repository\VoipEventRepositoryInterface;

/**
 * @extends ServiceEntityRepository<VoipEvent>
 */
class VoipEventDoctrineRepository extends ServiceEntityRepository implements VoipEventRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VoipEvent::class);
    }

    public function add(VoipEvent $event): void
    {
        $this->getEntityManager()->persist($event);
    }
}

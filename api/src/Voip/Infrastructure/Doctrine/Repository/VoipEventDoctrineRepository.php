<?php

declare(strict_types=1);

namespace App\Voip\Infrastructure\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Voip\Domain\Entity\VoipEvent;
use App\Voip\Domain\Repository\VoipEventRepositoryInterface;
use Override;

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

    #[Override]
    public function findOneById(int $id): ?VoipEvent
    {
        return $this->find($id);
    }

    public function findCursorPaginated(
        int $limit,
        ?int $cursor,
        string $sort,
        string $direction,
    ): array {
        $allowedSorts = [
            'id' => 'e.id',
            'occurredAt' => 'e.occurredAt',
            'receivedAt' => 'e.receivedAt',
            'callId' => 'e.callId',
            'type' => 'e.type',
            'source' => 'e.source',
        ];

        $sortField = $allowedSorts[$sort] ?? 'e.occurredAt';
        $direction = strtolower($direction) === 'asc' ? 'ASC' : 'DESC';

        $qb = $this->createQueryBuilder('e')
            ->setMaxResults($limit);

        if ($cursor !== null) {
            $qb
                ->andWhere($direction === 'DESC' ? 'e.id < :cursor' : 'e.id > :cursor')
                ->setParameter('cursor', $cursor);
        }

        $qb
            ->orderBy($sortField, $direction)
            ->addOrderBy('e.id', $direction);

        return $qb->getQuery()->getResult();
    }
}

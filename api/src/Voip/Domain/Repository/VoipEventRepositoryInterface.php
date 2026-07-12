<?php

declare(strict_types=1);

namespace App\Voip\Domain\Repository;

use App\Voip\Domain\Entity\VoipEvent;

interface VoipEventRepositoryInterface
{
    public function add(VoipEvent $event): void;

    public function findOneById(int $id): ?VoipEvent;

    public function findCursorPaginated(
        int $limit,
        ?int $cursor,
        string $sort,
        string $direction,
    ): array;
}

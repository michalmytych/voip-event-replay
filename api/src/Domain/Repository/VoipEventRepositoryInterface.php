<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Entity\VoipEvent;

interface VoipEventRepositoryInterface
{
    public function add(VoipEvent $event): void;
}

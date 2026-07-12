<?php

declare(strict_types=1);

namespace App\Voip\Domain\Model;

use App\Voip\Domain\Enum\CallStatus;

final readonly class ReconstructedCall
{
    public function __construct(
        public string $callId,
        public CallStatus $status,
        public ?\DateTimeImmutable $startedAt,
        public ?\DateTimeImmutable $endedAt,
        public ?int $waitingTimeSeconds,
        public ?int $talkTimeSeconds,
        public ?int $totalDurationSeconds,
        public ?string $agentId,
        public ?string $queue,
        public int $eventCount,
        public array $errors = [],
    ) {
    }
}

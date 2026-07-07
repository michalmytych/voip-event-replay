<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enum\VoipEventType;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateVoipEventRequest
{
    public function __construct(
        public ?string $externalEventId,

        #[Assert\NotBlank]
        public string $callId,

        #[Assert\NotNull]
        public VoipEventType $type,

        #[Assert\NotBlank]
        public string $source,

        #[Assert\NotBlank]
        #[Assert\DateTime(format: \DateTimeInterface::ATOM)]
        public string $occurredAt,

        public array $payload = [],

        #[Assert\PositiveOrZero]
        public ?int $sequenceNumber = null,
    ) {}
}

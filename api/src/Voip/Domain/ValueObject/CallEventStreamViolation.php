<?php

declare(strict_types=1);

namespace App\Voip\Application\ValueObject;

use App\Voip\Domain\Entity\VoipEvent;
use App\Voip\Domain\Enum\CallEventStreamViolationCode;

final readonly class CallEventStreamViolation
{
    public function __construct(
        private CallEventStreamViolationCode $code,
        private array $context = [],
    ) {}

    public function getCode(): CallEventStreamViolationCode
    {
        return $this->code;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}

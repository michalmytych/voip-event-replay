<?php

declare(strict_types=1);

namespace App\Voip\Domain\Enum;

enum CallStatus: string
{
    // @todo: validate if model is ok
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case MISSED = 'missed';
    case ABANDONED = 'abandoned';
    case INVALID = 'invalid';
}

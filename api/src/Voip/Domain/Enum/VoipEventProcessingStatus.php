<?php

declare(strict_types=1);

namespace App\Voip\Domain\Enum;

enum VoipEventProcessingStatus: string
{
    case PENDING = 'pending';
    case PROCESSED = 'processed';
    case FAILED = 'failed';
}

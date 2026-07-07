<?php

declare(strict_types=1);

namespace App\Enum;

enum VoipEventProcessingStatus: string
{
    case PENDING = 'pending';
    case PROCESSED = 'processed';
    case FAILED = 'failed';
}

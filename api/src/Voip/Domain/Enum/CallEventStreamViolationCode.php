<?php

declare(strict_types=1);

namespace App\Voip\Domain\Enum;

enum CallEventStreamViolationCode: string
{
    case DUPLICATE_SEQUENCE_NUMBER = 'duplicate_sequence_number';
    case NON_MONOTONIC_SEQUENCE = 'non_monotonic_sequence';
    case DUPLICATE_EXTERNAL_EVENT = 'duplicate_external_evnet';
}

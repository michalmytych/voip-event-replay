<?php

declare(strict_types=1);

namespace App\Voip\Domain\Enum;

enum VoipEventType: string
{
    case CALL_STARTED = 'CALL_STARTED';

    case QUEUE_JOINED = 'QUEUE_JOINED';

    case AGENT_RINGING = 'AGENT_RINGING';

    case AGENT_CONNECTED = 'AGENT_CONNECTED';

    case CALL_ENDED = 'CALL_ENDED';

    case CALL_MISSED = 'CALL_MISSED';

    case CALL_ABANDONED = 'CALL_ABANDONED';

    case TRANSFERRED = 'TRANSFERRED';
}

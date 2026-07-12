<?php

declare(strict_types=1);

namespace App\Voip\Domain\Enum;

enum VoipEventType: string
{
    // VoIP client initialized call at PBX
    case CALL_STARTED = 'CALL_STARTED';

    // Joined queue when PBX does not find a consultant instantly
    case QUEUE_JOINED = 'QUEUE_JOINED';

    // Consultant is found and his phone is ringing
    case AGENT_RINGING = 'AGENT_RINGING';

    // Consultant connected (waiting time finished, talk time starts)
    case AGENT_CONNECTED = 'AGENT_CONNECTED';

    // Talk is ended naturally by one of sideds
    case CALL_ENDED = 'CALL_ENDED';

    // Consultant did not connect or call timeout
    case CALL_MISSED = 'CALL_MISSED';

    // Client disconnected before client connected
    case CALL_ABANDONED = 'CALL_ABANDONED';

    // Call transferred to another number
    case TRANSFERRED = 'TRANSFERRED';
}

<?php

declare(strict_types=1);

namespace App\Voip\Domain\Service;

use App\Voip\Domain\Enum\VoipEventType;
use App\Voip\Domain\Model\ReconstructedCall;
use App\Voip\Domain\Model\VoipEventsStream;
use DomainException;

final class CallReconstructorService
{
    public function reconstruct(VoipEventsStream $eventsStream)//: ReconstructedCall
    {
        $violations = $eventsStream->validateIntegrity();

        // @todo: extract domain exceptions
        if (!empty($violations)) {
            throw new DomainException('VoIP events collection integrity is violated.'); // @todo: more context
        }

        if ($eventsStream->getFirstEvent()->getType() !== VoipEventType::CALL_STARTED) {
            throw new DomainException('First event of call should be ' . VoipEventType::CALL_STARTED->value);
        }

        $missed = $eventsStream->getEventsOfType(VoipEventType::CALL_MISSED);
        $abandoned = $eventsStream->getEventsOfType(VoipEventType::CALL_ABANDONED);
        $ended = $eventsStream->getEventsOfType(VoipEventType::CALL_ENDED);

        if (count([...$missed, ...$abandoned, ...$ended]) > 1) {
            throw new DomainException(
                'VoIP events collection should have only one call-terminating event (but has: '
                . 'missed: ' . count($missed) . ', '
                . 'abandoned: ' . count($abandoned) . ', '
                . 'ended: ' . count($ended) . ','
                . ')'
            );
        }

        if (!in_array($eventsStream->getLastEvent(), [VoipEventType::CALL_MISSED, VoipEventType::CALL_ABANDONED, VoipEventType::CALL_ENDED])) {
            // @todo: missing terminating-type event is not an error - call can be still "in progress"
            // instead check if stream has ony other-type events after terminating event (if has such)
        }

        // return new ReconstructedCall(
        //     callId: ,
        //     status: ,
        //     startedAt: ,
        //     endedAt: ,
        //     waitingTimeSeconds: ,
        //     talkTimeSeconds: ,
        //     totalDurationSeconds: ,
        //     agentId: ,
        //     queue: ,
        //     eventCount: ,
        //     errors: ,
        // );
    }
}

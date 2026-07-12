<?php

declare(strict_types=1);

namespace App\Voip\Domain\Service;

use App\Voip\Domain\Enum\VoipEventType;
use App\Voip\Domain\Entity\VoipEvent;
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
            throw new DomainException('First event of call should be ' . VoipEventType::CALL_STARTED->value . '.');
        }

        $missed = $eventsStream->getEventsOfType(VoipEventType::CALL_MISSED);
        $abandoned = $eventsStream->getEventsOfType(VoipEventType::CALL_ABANDONED);
        $ended = $eventsStream->getEventsOfType(VoipEventType::CALL_ENDED);

        $callTerminationEvents = [...$missed, ...$abandoned, ...$ended];

        if (count($callTerminationEvents) > 1) {
            throw new DomainException(
                'VoIP events collection should have only one call-terminating event (but has: '
                . 'missed: ' . count($missed) . ', '
                . 'abandoned: ' . count($abandoned) . ', '
                . 'ended: ' . count($ended) . ','
                . ').'
            );
        }

        if ($callTerminationEvents > 0 && !in_array($eventsStream->getLastEvent(), VoipEventType::CALL_TERMINATION_TYPES)) {
            throw new DomainException('Call terminating event should be a last event in the stream.');
        }

        foreach($eventsStream->getIterator() as $index => $event) {
            $isAgentConnectedEvent = $event->getType() === VoipEventType::AGENT_CONNECTED;

            /** @var VoipEvent|null $previousEvent */
            $previousEvent = $index >= 1 ? $eventsStream->getIterator()[$index - 1] : null;

            /** @var VoipEvent $event */
            if ($previousEvent != null && $isAgentConnectedEvent && $previousEvent->getType() !== VoipEventType::AGENT_RINGING) {
                throw new DomainException('Exactly before agent connected event should be agent ringing event.');
            }
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

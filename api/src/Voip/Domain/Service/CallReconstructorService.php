<?php

declare(strict_types=1);

namespace App\Voip\Domain\Service;

use App\Voip\Domain\Entity\VoipEvent;
use App\Voip\Domain\Enum\CallStatus;
use App\Voip\Domain\Enum\VoipEventType;
use App\Voip\Domain\Model\ReconstructedCall;
use App\Voip\Domain\Model\VoipEventsStream;
use DomainException;

final class CallReconstructorService
{
    private const CALL_TERMINATION_TYPES = [
        VoipEventType::CALL_ENDED,
        VoipEventType::CALL_MISSED,
        VoipEventType::CALL_ABANDONED,
    ];

    public function reconstruct(VoipEventsStream $eventsStream): ReconstructedCall
    {
        $violations = $eventsStream->validateIntegrity();

        if (count($violations) > 0) {
            throw new DomainException(
                sprintf(
                    'VoIP event stream integrity is violated: %s',
                    $this->formatIntegrityViolations(
                        $violations,
                    ),
                ),
            );
        }

        /** @var list<VoipEvent> $events */
        $events = array_values(
            iterator_to_array($eventsStream->getIterator(), false),
        );

        $this->validateCallFlow($eventsStream, $events);

        $startedEvent = $eventsStream->getFirstEvent();

        $connectedEvents = $eventsStream->getEventsOfType(
            VoipEventType::AGENT_CONNECTED,
        );

        $terminalEvent = $this->findTerminalEvent($eventsStream);

        $firstConnectedEvent = $connectedEvents[0] ?? null;
        $lastConnectedEvent = $connectedEvents !== []
            ? $connectedEvents[array_key_last($connectedEvents)]
            : null;

        $startedAt = $startedEvent->getOccurredAt();
        $connectedAt = $firstConnectedEvent?->getOccurredAt();
        $endedAt = $terminalEvent?->getOccurredAt();

        return new ReconstructedCall(
            callId: $eventsStream->getStreamCallId(),
            status: $this->resolveStatus($terminalEvent),
            startedAt: $startedAt,
            endedAt: $endedAt,
            waitingTimeSeconds: $this->secondsBetween($startedAt, $connectedAt),
            talkTimeSeconds: $this->secondsBetween($connectedAt, $endedAt),
            totalDurationSeconds: $this->secondsBetween($startedAt, $endedAt),
            agentId: $lastConnectedEvent !== null
                ? $this->extractStringFromPayload(
                    $lastConnectedEvent,
                    'agentId',
                ) : null,
            queue: $this->resolveLastQueue($eventsStream),
            eventCount: count($events),
            errors: [],
        );
    }

    /**
     * @param VoipEvent[] $events
     */
    private function validateCallFlow(VoipEventsStream $eventsStream, array $events): void
    {
        $violations = [];

        $callStartedEvents = $eventsStream->getEventsOfType(
            VoipEventType::CALL_STARTED,
        );

        if (count($callStartedEvents) === 0) {
            $violations[] = 'Missing CALL_STARTED event.';
        }

        if (count($callStartedEvents) > 1) {
            $violations[] = sprintf(
                'Only one CALL_STARTED event is allowed; found %d.',
                count($callStartedEvents),
            );
        }

        if ($eventsStream->getFirstEvent()->getType() !== VoipEventType::CALL_STARTED) {
            $violations[] = 'CALL_STARTED must be the first event.';
        }

        $terminationEvents = $this->getTerminationEvents($eventsStream);

        if (count($terminationEvents) > 1) {
            $violations[] = sprintf(
                'Only one terminating event is allowed; found %d.',
                count($terminationEvents),
            );
        }

        if (
            $terminationEvents !== []
            && !in_array($eventsStream->getLastEvent()->getType(), self::CALL_TERMINATION_TYPES, true)
        ) {
            $violations[] = 'The terminating event must be the last event.';
        }

        $agentWasRinging = false;
        $agentIsConnected = false;
        $callWasQueued = false;
        $callWasTerminated = false;

        foreach ($events as $index => $event) {
            $type = $event->getType();

            if ($callWasTerminated) {
                $violations[] = sprintf(
                    'Event %s at position %d occurred after call termination.',
                    $type->value,
                    $index,
                );

                continue;
            }

            switch ($type) {
                case VoipEventType::CALL_STARTED:
                    break;

                case VoipEventType::QUEUE_JOINED:
                    $callWasQueued = true;
                    $agentWasRinging = false;
                    break;

                case VoipEventType::AGENT_RINGING:
                    $agentWasRinging = true;
                    break;

                case VoipEventType::AGENT_CONNECTED:
                    if (!$agentWasRinging) {
                        $violations[] = sprintf(
                            'AGENT_CONNECTED at position %d must be preceded by AGENT_RINGING.',
                            $index,
                        );
                    }

                    $agentWasRinging = false;
                    $agentIsConnected = true;
                    break;

                case VoipEventType::TRANSFERRED:
                    if (!$agentIsConnected) {
                        $violations[] = sprintf(
                            'TRANSFERRED at position %d requires an active agent connection.',
                            $index,
                        );
                    }

                    $agentIsConnected = false;
                    $agentWasRinging = false;
                    $callWasQueued = false;
                    break;

                case VoipEventType::CALL_ABANDONED:
                    if (!$callWasQueued) {
                        $violations[] = sprintf(
                            'CALL_ABANDONED at position %d requires an earlier QUEUE_JOINED.',
                            $index,
                        );
                    }

                    if ($agentIsConnected) {
                        $violations[] = sprintf(
                            'CALL_ABANDONED at position %d cannot occur after an agent has connected.',
                            $index,
                        );
                    }

                    $callWasTerminated = true;
                    break;

                case VoipEventType::CALL_MISSED:
                    if ($agentIsConnected) {
                        $violations[] = sprintf(
                            'CALL_MISSED at position %d cannot occur after an agent has connected.',
                            $index,
                        );
                    }

                    $callWasTerminated = true;
                    break;

                case VoipEventType::CALL_ENDED:
                    $callWasTerminated = true;
                    break;
            }
        }

        if ($violations !== []) {
            throw new DomainException("Call flow is invalid:\n- " . implode("\n- ", $violations));
        }
    }

    /**
     * @return VoipEvent[]
     */
    private function getTerminationEvents(VoipEventsStream $eventsStream): array
    {
        return [
            ...$eventsStream->getEventsOfType(VoipEventType::CALL_ENDED),
            ...$eventsStream->getEventsOfType(VoipEventType::CALL_MISSED),
            ...$eventsStream->getEventsOfType(VoipEventType::CALL_ABANDONED),
        ];
    }

    private function findTerminalEvent(VoipEventsStream $eventsStream): ?VoipEvent
    {
        $terminationEvents = $this->getTerminationEvents($eventsStream);

        return $terminationEvents[0] ?? null;
    }

    private function resolveStatus(?VoipEvent $terminalEvent): CallStatus
    {
        return match ($terminalEvent?->getType()) {
            VoipEventType::CALL_ENDED => CallStatus::COMPLETED,
            VoipEventType::CALL_MISSED => CallStatus::MISSED,
            VoipEventType::CALL_ABANDONED => CallStatus::ABANDONED,
            null => CallStatus::IN_PROGRESS,
            default => throw new DomainException(
                'Unsupported call termination event.',
            ),
        };
    }

    private function resolveLastQueue(VoipEventsStream $eventsStream): ?string
    {
        $queueEvents = $eventsStream->getEventsOfType(VoipEventType::QUEUE_JOINED);

        if ($queueEvents === []) {
            return null;
        }

        $lastQueueEvent = $queueEvents[array_key_last($queueEvents)];

        return $this->extractStringFromPayload($lastQueueEvent, 'queue');
    }

    private function extractStringFromPayload(VoipEvent $event, string $key): ?string
    {
        $value = $event->getPayload()[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    private function secondsBetween(?\DateTimeImmutable $from, ?\DateTimeImmutable $to): ?int
    {
        if ($from === null || $to === null) {
            return null;
        }

        return max(0, $to->getTimestamp() - $from->getTimestamp());
    }

    private function formatIntegrityViolations(array $violations): string
    {
        return implode(
            ', ',
            array_map(
                static function (object $violation): string {
                    if (method_exists($violation, 'getCode')) {
                        $code = $violation->getCode();

                        return $code instanceof \BackedEnum ? (string) $code->value : (string) $code;
                    }

                    return $violation::class;
                },
                $violations,
            ),
        );
    }
}

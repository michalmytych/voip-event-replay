<?php

declare(strict_types=1);

namespace App\Voip\Domain\Model;

use App\Voip\Application\ValueObject\CallEventStreamViolation;
use App\Voip\Domain\Entity\VoipEvent;
use App\Voip\Domain\Enum\CallEventStreamViolationCode;
use App\Voip\Domain\Enum\VoipEventType;
use Countable;
use IteratorAggregate;

final class VoipEventsStream implements Countable, IteratorAggregate
{
    /**
     * @var VoipEvent[] $events
     */
    private array $events;

    /**
     * @param VoipEvent[] $events
     */
    public function __construct(array $events)
    {
        // Validate basic collection requirements: cannot be empty
        // also collection cannot exist with mixed call IDs

        if ($events === []) {
            throw new \InvalidArgumentException(
                'Call event stream cannot be empty.',
            );
        }

        $callId = $events[0]->getCallId();

        foreach ($events as $event) {
            if ($event->getCallId() !== $callId) {
                throw new \InvalidArgumentException(
                    'All events must have the same call ID.',
                );
            }
        }

        usort($events, self::compare(...));

        $this->events = array_values($events);
    }

    /**
     * @return CallEventStreamViolation[]
     */
    public function validateIntegrity(): array
    {
        return [
            ...$this->findDuplicateSequenceNumbers(),
            ...$this->findDuplicateExternalEvents(),
            ...$this->findSequenceOrderConflicts(),
        ];
    }

    public function getStreamCallId(): string
    {
        return $this->events[0]->getCallId();
    }

    public function count(): int
    {
        return count($this->events);
    }

    public function getIterator(): \Traversable
    {
        yield from $this->events;
    }

    public function getFirstEvent(): VoipEvent
    {
        return $this->events[0];
    }

    public function getLastEvent(): VoipEvent
    {
        return array_last($this->events);
    }

    public function getEventsOfType(VoipEventType $type): array
    {
        return array_values(
            array_filter($this->events, fn ($event): bool => $event->getType() === $type)
        );
    }

    /**
     * @return CallEventStreamViolation[]
     */
    private function findDuplicateSequenceNumbers(): array
    {
        $seen = [];
        $violations = [];

        foreach ($this->events as $event) {
            $sequenceNumber = $event->getSequenceNumber();

            if ($sequenceNumber === null) {
                continue;
            }

            if (isset($seen[$sequenceNumber])) {
                $violations[] = new CallEventStreamViolation(
                    code: CallEventStreamViolationCode::DUPLICATE_SEQUENCE_NUMBER,
                    context: [
                        'sequenceNumber' => $sequenceNumber,
                        'firstEventId' => $seen[$sequenceNumber]->getId(),
                        'duplicateEventId' => $event->getId(),
                    ],
                );

                continue;
            }

            $seen[$sequenceNumber] = $event;
        }

        return $violations;
    }

    /**
     * @return CallEventStreamViolation[]
     */
    private function findDuplicateExternalEvents(): array
    {
        $seen = [];
        $violations = [];

        foreach ($this->events as $event) {
            $externalEventId = $event->getExternalEventId();

            if ($externalEventId === null) {
                continue;
            }

            $key = $event->getSource() . ':' . $externalEventId;

            if (isset($seen[$key])) {
                $violations[] = new CallEventStreamViolation(
                    code: CallEventStreamViolationCode::DUPLICATE_EXTERNAL_EVENT,
                    context: [
                        'source' => $event->getSource(),
                        'externalEventId' => $externalEventId,
                        'firstEventId' => $seen[$key]->getId(),
                        'duplicateEventId' => $event->getId(),
                    ],
                );

                continue;
            }

            $seen[$key] = $event;
        }

        return $violations;
    }

    /**
     * @return CallEventStreamViolation[]
     */
    private function findSequenceOrderConflicts(): array
    {
        $violations = [];
        $previousEvent = null;
        $previousSequenceNumber = null;

        foreach ($this->events as $event) {
            $sequenceNumber = $event->getSequenceNumber();

            if ($sequenceNumber === null) {
                continue;
            }

            if ($previousSequenceNumber !== null && $sequenceNumber < $previousSequenceNumber)
            {
                $violations[] = new CallEventStreamViolation(
                    code: CallEventStreamViolationCode::NON_MONOTONIC_SEQUENCE,
                    context: [
                        'previousEventId' => $previousEvent?->getId(),
                        'previousSequenceNumber' => $previousSequenceNumber,
                        'currentEventId' => $event->getId(),
                        'currentSequenceNumber' => $sequenceNumber,
                    ],
                );
            }

            $previousEvent = $event;
            $previousSequenceNumber = $sequenceNumber;
        }

        return $violations;
    }

    private static function compare(VoipEvent $event, VoipEvent $anotherEvent): int
    {
        return [
            $event->getOccurredAt()->format('U.u'),
            $event->getSequenceNumber() ?? PHP_INT_MAX,
            $event->getId() ?? PHP_INT_MAX,
        ] <=> [
            $anotherEvent->getOccurredAt()->format('U.u'),
            $anotherEvent->getSequenceNumber() ?? PHP_INT_MAX,
            $anotherEvent->getId() ?? PHP_INT_MAX,
        ];
    }
}

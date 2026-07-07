<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\VoipEventDoctrineRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\VoipEventType;
use App\Enum\VoipEventProcessingStatus;

#[ORM\Entity(repositoryClass: VoipEventDoctrineRepository::class)]
#[ORM\Table(
    name: 'voip_events',
    indexes: [
        new ORM\Index(name: 'idx_voip_event_call_id', columns: ['call_id']),
        new ORM\Index(name: 'idx_voip_event_occurred_at', columns: ['occurred_at']),
        new ORM\Index(name: 'idx_voip_event_type', columns: ['type']),
    ],
    uniqueConstraints: [
        new ORM\UniqueConstraint(
            name: 'uniq_source_external_event',
            columns: ['source', 'external_event_id']
        ),
    ],
)]
class VoipEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $externalEventId = null;

    #[ORM\Column(length: 100)]
    private string $callId;

    #[ORM\Column(enumType: VoipEventType::class)]
    private VoipEventType $type;

    #[ORM\Column(length: 50)]
    private string $source;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, precision: 6)]
    private \DateTimeImmutable $occurredAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, precision: 6)]
    private \DateTimeImmutable $receivedAt;

    #[ORM\Column(type: Types::JSON)]
    private array $payload = [];

    #[ORM\Column(nullable: true)]
    private ?int $sequenceNumber = null;

    #[ORM\Column(enumType: VoipEventProcessingStatus::class)]
    private VoipEventProcessingStatus $processingStatus = VoipEventProcessingStatus::PENDING;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, precision: 6, nullable: true)]
    private ?\DateTimeImmutable $processedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $processingError = null;

    public function setExternalEventId(?string $externalEventId): self
    {
        $this->externalEventId = $externalEventId;

        return $this;
    }

    public function setCallId(string $callId): self
    {
        $this->callId = $callId;

        return $this;
    }

    public function setType(VoipEventType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function setSource(string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function setOccurredAt(\DateTimeImmutable $occurredAt): self
    {
        $this->occurredAt = $occurredAt;

        return $this;
    }

    public function setReceivedAt(\DateTimeImmutable $receivedAt): self
    {
        $this->receivedAt = $receivedAt;

        return $this;
    }

    public function setPayload(array $payload): self
    {
        $this->payload = $payload;

        return $this;
    }

    public function setSequenceNumber(?int $sequenceNumber): self
    {
        $this->sequenceNumber = $sequenceNumber;

        return $this;
    }

    public function setProcessingStatus(string $processingStatus): self
    {
        $this->processingStatus = $processingStatus;

        return $this;
    }

    public function setProcessedAt(?\DateTimeImmutable $processedAt): self
    {
        $this->processedAt = $processedAt;

        return $this;
    }

    public function setProcessingError(?string $processingError): self
    {
        $this->processingError = $processingError;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExternalEventId(): ?string
    {
        return $this->externalEventId;
    }

    public function getCallId(): string
    {
        return $this->callId;
    }

    public function getType(): VoipEventType
    {
        return $this->type;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function getReceivedAt(): \DateTimeImmutable
    {
        return $this->receivedAt;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getSequenceNumber(): ?int
    {
        return $this->sequenceNumber;
    }

    public function getProcessingStatus(): VoipEventProcessingStatus
    {
        return $this->processingStatus;
    }

    public function getProcessedAt(): ?\DateTimeImmutable
    {
        return $this->processedAt;
    }

    public function getProcessingError(): ?string
    {
        return $this->processingError;
    }
}

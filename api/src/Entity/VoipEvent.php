<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\VoipEventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VoipEventRepository::class)]
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

    #[ORM\Column(length: 50)]
    private string $type;

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

    #[ORM\Column(length: 20)]
    private string $processingStatus = 'pending';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, precision: 6, nullable: true)]
    private ?\DateTimeImmutable $processedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $processingError = null;
}

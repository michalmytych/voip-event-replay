<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\CreateVoipEventRequest;
use App\Entity\VoipEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use App\Domain\Repository\VoipEventRepositoryInterface;

#[Route('/voip-events')]
final class VoipEventController extends AbstractController
{
    public function __construct(private readonly VoipEventRepositoryInterface $voipEventRepository) {}

    #[Route('', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] CreateVoipEventRequest $request,
        EntityManagerInterface $em,
    ): JsonResponse {
        $event = new VoipEvent();

        $event->setExternalEventId($request->externalEventId);
        $event->setCallId($request->callId);
        $event->setType($request->type);
        $event->setSource($request->source);
        $event->setOccurredAt(new \DateTimeImmutable($request->occurredAt));
        $event->setReceivedAt(new \DateTimeImmutable('now')); // use Clock
        $event->setPayload($request->payload);
        $event->setSequenceNumber($request->sequenceNumber);

        $this->voipEventRepository->add($event);
        $em->flush();

        return $this->json([
            'id' => $event->getId(),
            'callId' => $event->getCallId(),
            'type' => $event->getType()->value,
            'source' => $event->getSource(),
            'occurredAt' => $event->getOccurredAt()->format(DATE_ATOM),
            'receivedAt' => $event->getReceivedAt()->format(DATE_ATOM),
            'payload' => $event->getPayload(),
            'sequenceNumber' => $event->getSequenceNumber(),
        ], 201);
    }
}

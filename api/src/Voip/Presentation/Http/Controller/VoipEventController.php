<?php

declare(strict_types=1);

namespace App\Voip\Presentation\Http\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use App\Voip\Application\DTO\CreateVoipEventRequest;
use App\Voip\Domain\Entity\VoipEvent;
use App\Voip\Domain\Repository\VoipEventRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;

#[Route('/voip-events')]
final class VoipEventController extends AbstractController
{
    public function __construct(private readonly VoipEventRepositoryInterface $voipEventRepository) {}

    #[Route('', methods: ['GET'])]
    public function collection(Request $request)
    {
        // @todo: validate limit value
        $limit = min(100, max(1, $request->query->getInt('limit', 50)));

        // @todo: validate cursor value
        $cursor = $request->query->get('cursor');
        $cursor = $cursor !== null ? (int) $cursor : null;

        // @todo: validate sort and direction values
        $sort = $request->query->get('sort', 'occurredAt');
        $direction = strtolower($request->query->get('direction', 'desc'));

        // @todo: to query
        $items = $this->voipEventRepository->findCursorPaginated(
            limit: $limit + 1,
            cursor: $cursor,
            sort: $sort,
            direction: $direction,
        );

        $hasMore = count($items) > $limit;
        $items = array_slice($items, 0, $limit);

        $nextCursor = $hasMore && $items !== []
            ? $items[array_key_last($items)]->getId()
            : null;

        return $this->json([
            'data' => array_map(fn(VoipEvent $event) => [
                'id' => $event->getId(),
                'externalEventId' => $event->getExternalEventId(),
                'callId' => $event->getCallId(),
                'type' => $event->getType()->value,
                'source' => $event->getSource(),
                'occurredAt' => $event->getOccurredAt()->format(DATE_ATOM),
                'receivedAt' => $event->getReceivedAt()->format(DATE_ATOM),
                'payload' => $event->getPayload(),
                'sequenceNumber' => $event->getSequenceNumber(),
            ], $items),
            'meta' => [
                'limit' => $limit,
                'cursor' => $cursor,
                'nextCursor' => $nextCursor,
                'hasMore' => $hasMore,
                'sort' => $sort,
                'direction' => $direction,
            ],
        ]);
    }

    #[Route('', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] CreateVoipEventRequest $request,
        EntityManagerInterface $em,
    ): JsonResponse {
        $event = new VoipEvent();

        // @todo: to command
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

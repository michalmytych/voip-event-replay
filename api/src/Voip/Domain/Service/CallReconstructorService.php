<?php

declare(strict_types=1);

namespace App\Voip\Domain\Service;

use App\Voip\Domain\Model\ReconstructedCall;

final class CallReconstructorService
{
    /**
     * @param VoipEvent[] $events
     */
    public function reconstruct(array $events)//: ReconstructedCall
    {
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

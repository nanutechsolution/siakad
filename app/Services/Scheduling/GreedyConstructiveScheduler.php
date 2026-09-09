<?php

namespace App\Services\Scheduling;

use App\Services\Scheduling\Support\DemandItem;
use App\Services\Scheduling\Support\ScheduleTracker;

class GreedyConstructiveScheduler
{
    public function __construct(
        protected CandidateGenerator $generator,
        protected CandidateScorer $scorer,
    ) {
    }

    /**
     * @param DemandItem[] $items sudah terurut most-constrained-first
     * @return array{assigned: array, failed: array}
     *   assigned: list of ['item' => DemandItem, 'candidate' => Candidate]
     *   failed: list of ['item' => DemandItem, 'reason' => string]
     */
    public function run(array $items, ScheduleTracker $tracker): array
    {
        $assigned = [];
        $failed = [];

        foreach ($items as $item) {
            $result = $this->generator->generate($item, $tracker);

            if (empty($result['candidates'])) {
                $failed[] = ['item' => $item, 'reason' => $result['reason']];
                continue;
            }

            $scored = $this->scorer->scoreAll($result['candidates'], $item, $tracker);
            $terbaik = $scored[0]; // sudah diurutkan skor terkecil = terbaik

            $tracker->reserve(
                $item->dosenIds,
                $item->kelasId,
                $terbaik->ruangId,
                $terbaik->hari,
                $terbaik->jamMulai,
                $terbaik->jamSelesai,
                $item->kelasProdiId
            );

            $assigned[] = ['item' => $item, 'candidate' => $terbaik];
        }

        return ['assigned' => $assigned, 'failed' => $failed];
    }
}

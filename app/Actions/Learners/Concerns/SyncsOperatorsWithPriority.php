<?php

namespace App\Actions\Learners\Concerns;

use App\Models\Learner;
use function collect;

trait SyncsOperatorsWithPriority
{
    private function syncOperatorsWithPriority(Learner $learner, array $operatorIds): void
    {
        $learner->operators()->sync($this->buildOperatorPriorityPayload($operatorIds));
    }

    /**
     * Preserve the provided ordering by assigning incremental priorities.
     */
    private function buildOperatorPriorityPayload(array $operatorIds): array
    {
        return collect($operatorIds)
            ->filter()
            ->values()
            ->mapWithKeys(fn (string $operatorId, int $index) => [
                $operatorId => ['priority' => $index + 1],
            ])
            ->all();
    }
}

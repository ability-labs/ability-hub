<?php

namespace App\Actions\Learners;

use App\Actions\Learners\Concerns\SyncsOperatorsWithPriority;
use App\Models\Learner;
use Illuminate\Support\Facades\DB;

class UpdateLearnerAction
{
    use SyncsOperatorsWithPriority;

    public function execute(Learner $learner, array $updateAttributes, ?array $operatorIds = null): Learner
    {
        return DB::transaction(function () use ($learner, $updateAttributes, $operatorIds) {
            $learner->fill($updateAttributes);
            $learner->save();

            if ($operatorIds !== null) {
                $this->syncOperatorsWithPriority($learner, $operatorIds);
            }

            return $learner;
        });
    }
}

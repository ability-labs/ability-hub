<?php

namespace App\Actions\Learners;

use App\Models\Learner;
use Illuminate\Support\Facades\DB;

class UpdateLearnerAction
{
    public function execute(Learner $learner, array $updateAttributes, ?array $operatorIds = null): Learner
    {
        return DB::transaction(function () use ($learner, $updateAttributes, $operatorIds) {
            $learner->fill($updateAttributes);
            $learner->save();

            if ($operatorIds !== null) {
                $learner->operators()->sync($operatorIds);
            }

            return $learner;
        });
    }
}

<?php

namespace App\Actions\Learners;

use App\Actions\Learners\Concerns\SyncsOperatorsWithPriority;
use App\Models\Learner;
use Illuminate\Support\Facades\DB;

class StoreLearnerAction
{
    use SyncsOperatorsWithPriority;

    public function execute(array $attributes, array $operatorIds = []): Learner
    {
        return DB::transaction(function () use ($attributes, $operatorIds) {
            $learner = new Learner();
            $learner->fill($attributes);
            $learner->save();

            $this->syncOperatorsWithPriority($learner, $operatorIds);

            return $learner;
        });
    }
}

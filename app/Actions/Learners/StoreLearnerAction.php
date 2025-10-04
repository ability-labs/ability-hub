<?php

namespace App\Actions\Learners;

use App\Models\Learner;
use Illuminate\Support\Facades\DB;

class StoreLearnerAction
{
    public function execute(array $attributes, array $operatorIds = []): Learner
    {
        return DB::transaction(function () use ($attributes, $operatorIds) {
            $learner = new Learner();
            $learner->fill($attributes);
            $learner->save();

            $learner->operators()->sync($operatorIds);

            return $learner;
        });
    }
}

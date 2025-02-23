<?php

namespace App\Actions;

use App\Models\Learner;

class StoreLearnerAction
{
    public function execute(array $attributes): Learner
    {
        $learner = new Learner();
        $learner->fill($attributes);
        $learner->save();

        return $learner;
    }
}

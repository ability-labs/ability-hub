<?php

namespace Tests\Feature\Learners;

use App\Actions\StoreLearnerAction;
use App\Actions\UpdateLearnerAction;
use App\Models\Learner;
use Tests\TestCase;

class LearnerActionsTest extends TestCase
{
    public function test_it_can_store_a_new_learner()
    {
        $action = new StoreLearnerAction();
        $attributes = Learner::factory()->make()->toArray();

        $learner = $action->execute($attributes);
        $this->assertModelExists($learner);
        $this->assertArrayIsEqualToArrayIgnoringListOfKeys(
            $attributes, $learner->toArray(),
            ['id', 'created_at', 'updated_at']
        );
    }

    public function test_it_can_update_an_existing_learner()
    {
        $action = new UpdateLearnerAction();
        $learner = Learner::factory()->create();
        $new_attributes = Learner::factory()->make()->toArray();

        $learner = $action->execute($learner, $new_attributes);
        $this->assertModelExists($learner);
        $this->assertArrayIsEqualToArrayIgnoringListOfKeys(
            $new_attributes, $learner->toArray(),
            ['id', 'created_at', 'updated_at']
        );
    }
}

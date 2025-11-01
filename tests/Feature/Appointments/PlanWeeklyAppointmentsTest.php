<?php

namespace Tests\Feature\Appointments;

use App\Actions\Appointments\PlanWeeklyAppointments;
use App\Models\Discipline;
use App\Models\Learner;
use App\Models\Operator;
use App\Models\Slot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PlanWeeklyAppointmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_schedules_learners_using_creation_order(): void
    {
        $user = User::factory()->create();
        $action = new PlanWeeklyAppointments();

        $weekStart = Carbon::parse('2025-06-23');

        $discipline = Discipline::factory()->create();
        $slot = Slot::factory()->for($discipline)->create([
            'duration_minutes' => 60,
            'week_day' => 1,
            'start_time_hour' => 9,
            'start_time_minute' => 0,
        ]);

        $operator = Operator::factory()->create();
        $operator->slots()->attach($slot->id);

        $firstLearner = Learner::factory()
            ->for($user)
            ->create([
                'created_at' => Carbon::parse('2025-01-01 10:00:00'),
                'updated_at' => Carbon::parse('2025-01-01 10:00:00'),
                'weekly_minutes' => 60,
            ]);
        $secondLearner = Learner::factory()
            ->for($user)
            ->create([
                'created_at' => Carbon::parse('2025-02-01 10:00:00'),
                'updated_at' => Carbon::parse('2025-02-01 10:00:00'),
                'weekly_minutes' => 60,
            ]);

        foreach ([$firstLearner, $secondLearner] as $learner) {
            $learner->slots()->attach($slot->id);
            $learner->operators()->attach($operator->id, ['priority' => 1]);
        }

        $result = $action->execute($user, $weekStart, collect([$secondLearner, $firstLearner]));

        $this->assertArrayHasKey($firstLearner->id, $result['appointments']);
        $this->assertArrayHasKey("learners.{$secondLearner->id}", $result['errors']);
    }
}

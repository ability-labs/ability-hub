<?php

namespace Tests\Feature\Appointments;

use App\Models\Learner;
use App\Models\User;
use App\Models\Operator;
use App\Models\Slot;
use App\Models\Discipline;
use App\Services\WeeklyPlannerService;
use App\Actions\Appointments\PlanWeeklyAppointments;
use App\Exceptions\WeeklyPlanException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class WeeklyPlannerBugTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_does_not_plan_appointments_for_learners_with_zero_weekly_minutes()
    {
        $user = User::factory()->create();
        $learner = Learner::factory()->create([
            'weekly_minutes' => 0,
        ]);
        
        $operator = Operator::factory()->create();
        $discipline = Discipline::factory()->create();
        
        // Give the learner an operator and some availability just in case
        $learner->operators()->attach($operator);
        $slot = Slot::create([
            'week_day' => 1, // Monday
            'start_time_hour' => 10,
            'start_time_minute' => 0,
            'end_time_hour' => 11,
            'end_time_minute' => 0,
            'duration_minutes' => 60,
            'day_span' => 'Morning',
            'discipline_id' => $discipline->id,
        ]);
        $learner->slots()->attach($slot);
        $operator->slots()->attach($slot);

        $service = new WeeklyPlannerService($user);
        
        $this->expectException(WeeklyPlanException::class);
        $this->expectExceptionMessage("minuti settimanali a 0.");
        
        $service->scheduleForLearner($learner, Carbon::parse('next monday'));
    }
    
    public function test_plan_action_reports_errors_for_learners_with_zero_minutes()
    {
        $user = User::factory()->create();
        $learner = Learner::factory()->create([
            'weekly_minutes' => 0,
        ]);
        
        $action = new PlanWeeklyAppointments();
        
        try {
            $result = $action->execute($user, Carbon::parse('next monday'), collect([$learner]));
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey("learners.{$learner->id}", $e->errors());
            $this->assertEquals("minuti settimanali a 0.", $e->errors()["learners.{$learner->id}"][0]);
            return;
        }
        
        $this->fail("ValidationException was not thrown");
    }
}

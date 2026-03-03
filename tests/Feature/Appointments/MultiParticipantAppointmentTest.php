<?php

namespace Tests\Feature\Appointments;

use App\Models\Appointment;
use App\Models\Discipline;
use App\Models\Learner;
use App\Models\Operator;
use App\Models\User;
use App\Services\WeeklyPlannerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MultiParticipantAppointmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected WeeklyPlannerService $plannerService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->plannerService = new WeeklyPlannerService($this->user);
    }

    public function test_it_can_create_appointment_with_multiple_participants_via_api(): void
    {
        $this->actingAs($this->user);

        $operators = Operator::factory()->count(2)->create();
        $learners = Learner::factory()->count(2)->create();
        $discipline = Discipline::factory()->create();

        $payload = [
            'operator_ids' => $operators->pluck('id')->toArray(),
            'learner_ids' => $learners->pluck('id')->toArray(),
            'discipline_id' => $discipline->id,
            'starts_at' => now()->addDay()->setHour(10)->setMinute(0)->toDateTimeString(),
            'ends_at' => now()->addDay()->setHour(11)->setMinute(0)->toDateTimeString(),
            'comments' => 'Multi test'
        ];

        $response = $this->postJson('/appointments', $payload);

        $response->assertStatus(200);
        
        $appointment = Appointment::first();
        $this->assertCount(2, $appointment->operators);
        $this->assertCount(2, $appointment->learners);
        
        // Title should contain all names
        $expectedTitle = $learners->pluck('full_name')->join(', ') . ' (' . $operators->pluck('name')->join(', ') . ')';
        $this->assertEquals($expectedTitle, $appointment->title);
    }

    public function test_conflict_detection_works_for_multi_participant_appointments(): void
    {
        $operators = Operator::factory()->count(2)->create();
        $learners = Learner::factory()->count(2)->create();
        $discipline = Discipline::factory()->create();

        $start = Carbon::parse('2025-06-23 10:00:00');
        $end = Carbon::parse('2025-06-23 11:30:00');

        // Create a multi-participant appointment
        $appointment = Appointment::create([
            'user_id' => $this->user->id,
            'discipline_id' => $discipline->id,
            'operator_id' => $operators[0]->id,
            'learner_id' => $learners[0]->id,
            'starts_at' => $start,
            'ends_at' => $end,
            'title' => 'Existing',
            'duration_minutes' => 90
        ]);
        $appointment->operators()->syncWithoutDetaching($operators->pluck('id'));
        $appointment->learners()->syncWithoutDetaching($learners->pluck('id'));

        // Check conflicts in PlannerService
        // 1. Conflict for one of the operators (operators[1])
        $newStart = Carbon::parse('2025-06-23 11:00:00');
        $newEnd = Carbon::parse('2025-06-23 12:00:00');
        
        // This should return true because operators[1] is busy until 11:30
        $hasConflict = (new \ReflectionClass($this->plannerService))
            ->getMethod('hasConflictingAppointment')
            ->getClosure($this->plannerService)($learners[0], $operators[1], $newStart, $newEnd);

        $this->assertTrue($hasConflict, "Conflict NOT detected for busy operator in multi-participant appointment");

        // 2. Conflict for one of the learners (learners[1])
        $hasConflictLearner = (new \ReflectionClass($this->plannerService))
            ->getMethod('hasConflictingAppointment')
            ->getClosure($this->plannerService)($learners[1], Operator::factory()->create(), $newStart, $newEnd);

        $this->assertTrue($hasConflictLearner, "Conflict NOT detected for busy learner in multi-participant appointment");
    }
}

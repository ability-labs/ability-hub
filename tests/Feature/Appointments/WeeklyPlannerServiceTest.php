<?php

namespace Tests\Feature\Appointments;

use App\Exceptions\WeeklyPlanException;
use App\Models\Appointment;
use App\Models\Discipline;
use App\Models\Learner;
use App\Models\Operator;
use App\Models\Slot;
use App\Models\User;
use App\Services\WeeklyPlannerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class WeeklyPlannerServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $testUser;
    protected WeeklyPlannerService $plannerService;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a test user for the service
        $this->testUser = User::factory()->create();
        // Initialize the WeeklyPlannerService with the test user
        $this->plannerService = new WeeklyPlannerService($this->testUser);
    }

    private function createLearnerWithOperators(array $attributes, Operator ...$operators): Learner
    {
        $learner = Learner::factory()->create($attributes);

        $prioritizedOperators = collect($operators)
            ->values()
            ->mapWithKeys(fn (Operator $operator, int $index) => [
                $operator->id => ['priority' => $index + 1],
            ]);

        $learner->operators()->sync($prioritizedOperators->all());

        return $learner->fresh('operators');
    }

    public function test_it_can_schedule_fully_declared_learner_with_basic_slots_configuration(): void
    {
        // Setup: Define a Monday date for the start of the week
        $weekStartDate = Carbon::parse('2025-06-23'); // Monday

        // Create a discipline and three specific slots for Monday, Tuesday, and Wednesday
        $discipline = Discipline::factory()->create();
        $slots = Slot::factory()->for($discipline)->count(3)->sequence(
            ['duration_minutes' => 60, 'week_day' => 1, 'start_time_hour' => 9, 'start_time_minute' => 0], // Monday 09:00
            ['duration_minutes' => 60, 'week_day' => 2, 'start_time_hour' => 10, 'start_time_minute' => 0], // Tuesday 10:00
            ['duration_minutes' => 60, 'week_day' => 3, 'start_time_hour' => 11, 'start_time_minute' => 0], // Wednesday 11:00
        )->create();

        // Create an operator and a learner with a target of 180 weekly minutes
        $operator = Operator::factory()->create();
        $learner = $this->createLearnerWithOperators(['weekly_minutes' => 180], $operator);

        // Attach all created slots to both the learner and the operator
        foreach ($slots as $slot) {
            $learner->slots()->attach($slot->id);
            $operator->slots()->attach($slot->id);
        }

        // Act: Schedule appointments for the learner for the specified week
        $this->plannerService->scheduleForLearner($learner, $weekStartDate);

        // Assert: Verify that 3 appointments were created and their total duration is 180 minutes
        $this->assertEquals(3, $learner->appointments()->count());
        $this->assertEquals(180, $learner->appointments()->sum('duration_minutes'));

        // Verify that the appointment times correctly correspond to the slot definitions
        $appointments = $learner->appointments()->orderBy('starts_at')->get();
        $this->assertEquals('2025-06-23 09:00:00', $appointments[0]->starts_at->format('Y-m-d H:i:s'));
        $this->assertEquals('2025-06-24 10:00:00', $appointments[1]->starts_at->format('Y-m-d H:i:s'));
        $this->assertEquals('2025-06-25 11:00:00', $appointments[2]->starts_at->format('Y-m-d H:i:s'));
    }

    public function test_it_normalizes_slot_times_with_timezone_offset(): void
    {
        $originalConfigTz = config('app.timezone');
        $originalPhpTz = date_default_timezone_get();
        $targetTimezone = 'Europe/Rome';

        config(['app.timezone' => $targetTimezone]);
        date_default_timezone_set($targetTimezone);

        try {
            $discipline = Discipline::factory()->create();
            $slot = Slot::factory()->for($discipline)->create([
                'duration_minutes' => 60,
                'week_day' => 1,
                'start_time_hour' => 9,
                'start_time_minute' => 0,
            ]);

            $operator = Operator::factory()->create();
            $operator->slots()->attach($slot->id);

            $learner = $this->createLearnerWithOperators(['weekly_minutes' => 60], $operator);
            $learner->slots()->attach($slot->id);
            $learner->load('slots', 'operators');

            $weekStart = Carbon::parse('2025-06-23', $targetTimezone);

            $this->plannerService->scheduleForLearner($learner, $weekStart);

            $appointment = $learner->appointments()->first();

            $this->assertNotNull($appointment);
            $this->assertEquals('09:00', $appointment->starts_at->copy()->setTimezone($targetTimezone)->format('H:i'));
            $this->assertEquals('07:00', $appointment->starts_at->copy()->setTimezone('UTC')->format('H:i'));
        } finally {
            config(['app.timezone' => $originalConfigTz]);
            date_default_timezone_set($originalPhpTz ?: 'UTC');
        }
    }

    public function test_it_uses_next_operator_when_first_is_busy_on_same_slot(): void
    {
        $weekStart = Carbon::parse('2025-07-07');
        $discipline = Discipline::factory()->create();

        $sharedSlot = Slot::factory()->for($discipline)->create([
            'duration_minutes' => 60,
            'week_day' => 1,
            'start_time_hour' => 9,
            'start_time_minute' => 0,
        ]);

        $primaryOperator = Operator::factory()->create();
        $secondaryOperator = Operator::factory()->create();

        $learner = $this->createLearnerWithOperators(['weekly_minutes' => 60], $primaryOperator, $secondaryOperator);
        $learner->slots()->attach($sharedSlot->id);

        $primaryOperator->slots()->attach($sharedSlot->id);
        $secondaryOperator->slots()->attach($sharedSlot->id);

        $otherLearner = Learner::factory()->create();

        Appointment::factory()->for($primaryOperator, 'operator')
            ->for($otherLearner, 'learner')
            ->create([
                'starts_at' => $weekStart->copy()->setTime(9, 0),
                'ends_at' => $weekStart->copy()->setTime(10, 0),
                'duration_minutes' => 60,
            ]);

        $this->plannerService->scheduleForLearner($learner->fresh('slots', 'operators'), $weekStart);

        $appointments = $learner->appointments()->get();

        $this->assertCount(1, $appointments);
        $this->assertEquals($secondaryOperator->id, $appointments->first()->operator_id);
        $this->assertEquals('2025-07-07 09:00:00', $appointments->first()->starts_at->format('Y-m-d H:i:s'));
    }

    public function test_it_uses_same_time_slot_from_next_operator_even_with_different_slot_records(): void
    {
        $weekStart = Carbon::parse('2025-07-07');
        $discipline = Discipline::factory()->create();

        $learnerSlot = Slot::factory()->for($discipline)->create([
            'duration_minutes' => 60,
            'week_day' => 3,
            'start_time_hour' => 17,
            'start_time_minute' => 0,
        ]);

        $primaryOperator = Operator::factory()->create();
        $secondaryOperator = Operator::factory()->create();

        $secondarySlot = Slot::factory()->for($discipline)->create([
            'duration_minutes' => 60,
            'week_day' => 3,
            'start_time_hour' => 17,
            'start_time_minute' => 0,
        ]);

        $learner = $this->createLearnerWithOperators(['weekly_minutes' => 60], $primaryOperator, $secondaryOperator);
        $learner->slots()->attach($learnerSlot->id);

        $primaryOperator->slots()->attach($learnerSlot->id);
        $secondaryOperator->slots()->attach($secondarySlot->id);

        $otherLearner = Learner::factory()->create();

        Appointment::factory()->for($primaryOperator, 'operator')
            ->for($otherLearner, 'learner')
            ->create([
                'starts_at' => $weekStart->copy()->addDays($learnerSlot->week_day - 1)->setTime(17, 0),
                'ends_at' => $weekStart->copy()->addDays($learnerSlot->week_day - 1)->setTime(18, 0),
                'duration_minutes' => 60,
            ]);

        $this->plannerService->scheduleForLearner($learner->fresh('slots', 'operators'), $weekStart);

        $appointments = $learner->appointments()->get();

        $this->assertCount(1, $appointments);
        $this->assertEquals($secondaryOperator->id, $appointments->first()->operator_id);
        $this->assertEquals('2025-07-09 17:00:00', $appointments->first()->starts_at->format('Y-m-d H:i:s'));
    }

    public function test_it_falls_back_to_alternative_slot_when_all_operators_conflict_on_preferred_one(): void
    {
        $weekStart = Carbon::parse('2026-01-05');
        $discipline = Discipline::factory()->create();

        $preferredSlot = Slot::factory()->for($discipline)->create([
            'duration_minutes' => 60,
            'week_day' => 1,
            'start_time_hour' => 9,
            'start_time_minute' => 0,
        ]);

        $fallbackSlot = Slot::factory()->for($discipline)->create([
            'duration_minutes' => 60,
            'week_day' => 1,
            'start_time_hour' => 10,
            'start_time_minute' => 0,
        ]);

        $primaryOperator = Operator::factory()->create();
        $secondaryOperator = Operator::factory()->create();

        $learner = $this->createLearnerWithOperators(['weekly_minutes' => 60], $primaryOperator, $secondaryOperator);
        $learner->slots()->attach($preferredSlot->id);

        $primaryOperator->slots()->attach($preferredSlot->id);
        $secondaryOperator->slots()->attach([$preferredSlot->id, $fallbackSlot->id]);

        $otherLearner = Learner::factory()->create();

        Appointment::factory()->for($primaryOperator, 'operator')
            ->for($otherLearner, 'learner')
            ->create([
                'starts_at' => $weekStart->copy()->setTime(9, 0),
                'ends_at' => $weekStart->copy()->setTime(10, 0),
                'duration_minutes' => 60,
            ]);

        Appointment::factory()->for($secondaryOperator, 'operator')
            ->for($otherLearner, 'learner')
            ->create([
                'starts_at' => $weekStart->copy()->setTime(9, 0),
                'ends_at' => $weekStart->copy()->setTime(10, 0),
                'duration_minutes' => 60,
            ]);

        $this->plannerService->scheduleForLearner($learner->fresh('slots', 'operators'), $weekStart);

        $appointments = $learner->appointments()->get();

        $this->assertCount(1, $appointments);
        $this->assertEquals($secondaryOperator->id, $appointments->first()->operator_id);
        $this->assertEquals('2026-01-05 10:00:00', $appointments->first()->starts_at->format('Y-m-d H:i:s'));
    }

    public function test_it_honours_learner_slots_even_if_operator_has_earlier_availability(): void
    {
        $weekStart = Carbon::parse('2025-10-06'); // Monday
        $discipline = Discipline::factory()->create();

        // Learner declares three 90-minute slots
        $learnerSlots = collect([
            Slot::factory()->for($discipline)->create([
                'duration_minutes' => 90,
                'week_day' => 1,
                'start_time_hour' => 12,
                'start_time_minute' => 0,
            ]), // Monday 12:00-13:30
            Slot::factory()->for($discipline)->create([
                'duration_minutes' => 90,
                'week_day' => 3,
                'start_time_hour' => 12,
                'start_time_minute' => 0,
            ]), // Wednesday 12:00-13:30
            Slot::factory()->for($discipline)->create([
                'duration_minutes' => 90,
                'week_day' => 5,
                'start_time_hour' => 17,
                'start_time_minute' => 0,
            ]), // Friday 17:00-18:30
        ]);

        // Operator declares a full set of slots including earlier times
        $operatorOnlySlots = collect([
            Slot::factory()->for($discipline)->create([
                'duration_minutes' => 90,
                'week_day' => 1,
                'start_time_hour' => 9,
                'start_time_minute' => 0,
            ]),
            Slot::factory()->for($discipline)->create([
                'duration_minutes' => 90,
                'week_day' => 1,
                'start_time_hour' => 10,
                'start_time_minute' => 30,
            ]),
            Slot::factory()->for($discipline)->create([
                'duration_minutes' => 90,
                'week_day' => 2,
                'start_time_hour' => 9,
                'start_time_minute' => 0,
            ]),
            Slot::factory()->for($discipline)->create([
                'duration_minutes' => 90,
                'week_day' => 2,
                'start_time_hour' => 10,
                'start_time_minute' => 30,
            ]),
            Slot::factory()->for($discipline)->create([
                'duration_minutes' => 90,
                'week_day' => 4,
                'start_time_hour' => 14,
                'start_time_minute' => 0,
            ]),
            Slot::factory()->for($discipline)->create([
                'duration_minutes' => 90,
                'week_day' => 4,
                'start_time_hour' => 15,
                'start_time_minute' => 30,
            ]),
            Slot::factory()->for($discipline)->create([
                'duration_minutes' => 90,
                'week_day' => 4,
                'start_time_hour' => 17,
                'start_time_minute' => 0,
            ]),
            Slot::factory()->for($discipline)->create([
                'duration_minutes' => 90,
                'week_day' => 4,
                'start_time_hour' => 18,
                'start_time_minute' => 30,
            ]),
            Slot::factory()->for($discipline)->create([
                'duration_minutes' => 90,
                'week_day' => 5,
                'start_time_hour' => 14,
                'start_time_minute' => 0,
            ]),
            Slot::factory()->for($discipline)->create([
                'duration_minutes' => 90,
                'week_day' => 5,
                'start_time_hour' => 15,
                'start_time_minute' => 30,
            ]),
        ]);

        $operator = Operator::factory()->create();
        $learner = $this->createLearnerWithOperators(['weekly_minutes' => 270], $operator);

        // Attach learner slots to both learner and operator, operator-only slots just to the operator
        foreach ($learnerSlots as $slot) {
            $learner->slots()->attach($slot->id);
            $operator->slots()->attach($slot->id);
        }

        foreach ($operatorOnlySlots as $slot) {
            $operator->slots()->attach($slot->id);
        }

        $this->plannerService->scheduleForLearner($learner, $weekStart);

        $appointments = $learner->appointments()->orderBy('starts_at')->get();

        $this->assertCount(3, $appointments);
        $this->assertEquals(270, $appointments->sum('duration_minutes'));

        // Ensure appointments align with the learner-declared slots (not the earlier operator slots)
        $this->assertEquals('2025-10-06 12:00:00', $appointments[0]->starts_at->format('Y-m-d H:i:s'));
        $this->assertEquals('2025-10-08 12:00:00', $appointments[1]->starts_at->format('Y-m-d H:i:s'));
        $this->assertEquals('2025-10-10 17:00:00', $appointments[2]->starts_at->format('Y-m-d H:i:s'));
    }

    public function test_it_prioritizes_learner_slots_even_without_operator_overlap(): void
    {
        $weekStart = Carbon::parse('2025-06-23');
        $discipline = Discipline::factory()->create();

        // Learner chooses three specific slots across the week
        $learnerSlots = Slot::factory()->for($discipline)->count(3)->sequence(
            ['duration_minutes' => 60, 'week_day' => 1, 'start_time_hour' => 9, 'start_time_minute' => 0], // Monday 09:00
            ['duration_minutes' => 60, 'week_day' => 2, 'start_time_hour' => 10, 'start_time_minute' => 0], // Tuesday 10:00
            ['duration_minutes' => 60, 'week_day' => 3, 'start_time_hour' => 11, 'start_time_minute' => 0], // Wednesday 11:00
        )->create();

        // Operator declares availability on different days
        $operatorSlots = Slot::factory()->for($discipline)->count(3)->sequence(
            ['duration_minutes' => 60, 'week_day' => 4, 'start_time_hour' => 9, 'start_time_minute' => 0], // Thursday 09:00
            ['duration_minutes' => 60, 'week_day' => 5, 'start_time_hour' => 9, 'start_time_minute' => 0], // Friday 09:00
            ['duration_minutes' => 60, 'week_day' => 6, 'start_time_hour' => 9, 'start_time_minute' => 0], // Saturday 09:00
        )->create();

        $operator = Operator::factory()->create();
        $learner = $this->createLearnerWithOperators(['weekly_minutes' => 180], $operator);

        foreach ($learnerSlots as $slot) {
            $learner->slots()->attach($slot->id);
        }
        foreach ($operatorSlots as $slot) {
            $operator->slots()->attach($slot->id);
        }

        $this->plannerService->scheduleForLearner($learner, $weekStart);

        $appointments = $learner->appointments()->orderBy('starts_at')->get();

        $this->assertCount(3, $appointments);
        $this->assertEquals(180, $appointments->sum('duration_minutes'));

        $this->assertEquals('2025-06-26 09:00:00', $appointments[0]->starts_at->format('Y-m-d H:i:s'));
        $this->assertEquals('2025-06-27 09:00:00', $appointments[1]->starts_at->format('Y-m-d H:i:s'));
        $this->assertEquals('2025-06-28 09:00:00', $appointments[2]->starts_at->format('Y-m-d H:i:s'));
    }

    public function test_it_falls_back_to_operator_slots_after_using_learner_preferences(): void
    {
        $weekStart = Carbon::parse('2025-06-23');
        $discipline = Discipline::factory()->create();

        $learnerPreferredSlots = Slot::factory()->for($discipline)->count(2)->sequence(
            ['duration_minutes' => 60, 'week_day' => 1, 'start_time_hour' => 9, 'start_time_minute' => 0], // Monday 09:00
            ['duration_minutes' => 60, 'week_day' => 2, 'start_time_hour' => 9, 'start_time_minute' => 0], // Tuesday 09:00
        )->create();

        $operatorFallbackSlots = Slot::factory()->for($discipline)->count(2)->sequence(
            ['duration_minutes' => 60, 'week_day' => 3, 'start_time_hour' => 9, 'start_time_minute' => 0], // Wednesday 09:00
            ['duration_minutes' => 60, 'week_day' => 4, 'start_time_hour' => 9, 'start_time_minute' => 0], // Thursday 09:00
        )->create();

        $operator = Operator::factory()->create();
        $learner = $this->createLearnerWithOperators(['weekly_minutes' => 180], $operator);

        foreach ($learnerPreferredSlots as $slot) {
            $learner->slots()->attach($slot->id);
            $operator->slots()->attach($slot->id);
        }

        foreach ($operatorFallbackSlots as $slot) {
            $operator->slots()->attach($slot->id);
        }

        $this->plannerService->scheduleForLearner($learner, $weekStart);

        $appointments = $learner->appointments()->orderBy('starts_at')->get();

        $this->assertCount(3, $appointments);
        $this->assertEquals(180, $appointments->sum('duration_minutes'));

        // First two appointments must match learner preferred slots (Mon/Tue)
        $this->assertEquals('2025-06-23 09:00:00', $appointments[0]->starts_at->format('Y-m-d H:i:s'));
        $this->assertEquals('2025-06-24 09:00:00', $appointments[1]->starts_at->format('Y-m-d H:i:s'));

        // Remaining minutes should be fulfilled using operator fallback availability
        $this->assertEquals('2025-06-25 09:00:00', $appointments[2]->starts_at->format('Y-m-d H:i:s'));
    }

    public function test_it_handles_learner_with_no_declared_availability(): void
    {
        // Create a discipline and some slots
        $discipline = Discipline::factory()->create();
        $slots = Slot::factory()->for($discipline)->count(3)->sequence(
            ['duration_minutes' => 60, 'week_day' => 1, 'start_time_hour' => 9, 'start_time_minute' => 0],
            ['duration_minutes' => 90, 'week_day' => 2, 'start_time_hour' => 10, 'start_time_minute' => 0],
            ['duration_minutes' => 30, 'week_day' => 3, 'start_time_hour' => 11, 'start_time_minute' => 0],
        )->create();

        // Create an operator and a learner with a target of 180 weekly minutes
        $operator = Operator::factory()->create();
        $learner = $this->createLearnerWithOperators(['weekly_minutes' => 180], $operator);

        // Important: Only the operator has slots declared; the learner has none attached.
        foreach ($slots as $slot) {
            $operator->slots()->attach($slot->id);
        }

        // Act: Schedule appointments for the learner
        $weekStart = Carbon::parse('2025-06-23');
        $this->plannerService->scheduleForLearner($learner, $weekStart);

        // Assert: Verify that appointments were still created, relying on the operator's availability
        $this->assertEquals(3, $learner->appointments()->count());
        $this->assertEquals(180, $learner->appointments()->sum('duration_minutes'));
    }

    public function test_it_picks_matching_operator_before_fallback_when_multiple_assigned(): void
    {
        $weekStart = Carbon::parse('2025-07-07');
        $discipline = Discipline::factory()->create();

        $preferredSlot = Slot::factory()->for($discipline)->create([
            'duration_minutes' => 60,
            'week_day' => 1,
            'start_time_hour' => 10,
            'start_time_minute' => 0,
        ]);

        $fallbackSlot = Slot::factory()->for($discipline)->create([
            'duration_minutes' => 60,
            'week_day' => 1,
            'start_time_hour' => 11,
            'start_time_minute' => 0,
        ]);

        $operatorA = Operator::factory()->create(['name' => 'Alpha Operator']);
        $operatorB = Operator::factory()->create(['name' => 'Beta Operator']);

        $learner = $this->createLearnerWithOperators(['weekly_minutes' => 60], $operatorA, $operatorB);

        $learner->slots()->attach($preferredSlot->id);
        $operatorA->slots()->attach($fallbackSlot->id);
        $operatorB->slots()->attach($preferredSlot->id);

        $this->plannerService->scheduleForLearner($learner, $weekStart);

        $appointment = $learner->appointments()->first();

        $this->assertNotNull($appointment);
        $this->assertEquals($operatorB->id, $appointment->operator_id);
        $this->assertEquals('2025-07-07 10:00:00', $appointment->starts_at->format('Y-m-d H:i:s'));
    }

    public function test_it_uses_operator_priority_when_only_fallback_slots_are_available(): void
    {
        $weekStart = Carbon::parse('2025-07-07');
        $discipline = Discipline::factory()->create();

        $highPriorityOperator = Operator::factory()->create(['name' => 'Zulu Operator']);
        $lowPriorityOperator = Operator::factory()->create(['name' => 'Alpha Operator']);

        $learner = $this->createLearnerWithOperators(['weekly_minutes' => 60], $highPriorityOperator, $lowPriorityOperator);

        $highPrioritySlot = Slot::factory()->for($discipline)->create([
            'duration_minutes' => 60,
            'week_day' => 1,
            'start_time_hour' => 9,
            'start_time_minute' => 0,
        ]);

        $lowPrioritySlot = Slot::factory()->for($discipline)->create([
            'duration_minutes' => 60,
            'week_day' => 2,
            'start_time_hour' => 9,
            'start_time_minute' => 0,
        ]);

        $highPriorityOperator->slots()->attach($highPrioritySlot->id);
        $lowPriorityOperator->slots()->attach($lowPrioritySlot->id);

        $this->plannerService->scheduleForLearner($learner, $weekStart);

        $appointment = $learner->appointments()->first();

        $this->assertNotNull($appointment);
        $this->assertEquals($highPriorityOperator->id, $appointment->operator_id);
        $this->assertEquals('2025-07-07 09:00:00', $appointment->starts_at->format('Y-m-d H:i:s'));
    }

    public function test_it_handles_partial_weekly_minutes_scheduling(): void
    {
        // Create a discipline and two slots totaling 90 minutes
        $discipline = Discipline::factory()->create();
        $slots = Slot::factory()->for($discipline)->count(2)->sequence(
            ['duration_minutes' => 60, 'week_day' => 1, 'start_time_hour' => 9, 'start_time_minute' => 0],
            ['duration_minutes' => 30, 'week_day' => 2, 'start_time_hour' => 10, 'start_time_minute' => 0],
        )->create();

        // Create an operator and a learner with a target of 180 weekly minutes
        $operator = Operator::factory()->create();
        $learner = $this->createLearnerWithOperators(['weekly_minutes' => 180], $operator);

        // Attach these 90 minutes of slots to both the learner and the operator
        foreach ($slots as $slot) {
            $learner->slots()->attach($slot->id);
            $operator->slots()->attach($slot->id);
        }

        // Act: Schedule appointments for the learner
        $weekStart = Carbon::parse('2025-06-23');
        $this->plannerService->scheduleForLearner($learner, $weekStart);

        // Assert: Verify that only the available 90 minutes were scheduled out of the requested 180
        $this->assertEquals(2, $learner->appointments()->count());
        $this->assertEquals(90, $learner->appointments()->sum('duration_minutes'));

        // Verify the 'remaining_minutes' in the scheduling summary
        $summary = $this->plannerService->getSchedulingSummary($learner, $weekStart);
        $this->assertEquals(90, $summary['remaining_minutes']); // 180 (target) - 90 (scheduled) = 90 remaining
    }

    public function test_it_respects_existing_appointments_when_scheduling(): void
    {
        // Create a discipline and three 60-minute slots (total 180 minutes)
        $discipline = Discipline::factory()->create();
        $slots = Slot::factory()->for($discipline)->count(3)->sequence(
            ['duration_minutes' => 60, 'week_day' => 1, 'start_time_hour' => 9, 'start_time_minute' => 0],
            ['duration_minutes' => 60, 'week_day' => 2, 'start_time_hour' => 10, 'start_time_minute' => 0],
            ['duration_minutes' => 60, 'week_day' => 3, 'start_time_hour' => 11, 'start_time_minute' => 0],
        )->create();

        // Create an operator and a learner with a target of 180 weekly minutes
        $operator = Operator::factory()->create();
        $learner = $this->createLearnerWithOperators(['weekly_minutes' => 180], $operator);

        // Attach all slots to both the learner and the operator
        foreach ($slots as $slot) {
            $learner->slots()->attach($slot->id);
            $operator->slots()->attach($slot->id);
        }

        // Set the start of the week for scheduling
        $weekStart = Carbon::parse('2025-06-23');

        // Create an existing appointment for 60 minutes that matches one of the slots
        Appointment::factory()->create([
            'user_id' => $this->testUser->id,
            'learner_id' => $learner->id,
            'operator_id' => $operator->id,
            'starts_at' => $weekStart->copy()->setTime(9, 0), // Monday 9:00
            'ends_at' => $weekStart->copy()->setTime(10, 0),
            'duration_minutes' => 60,
        ]);

        // Act: Schedule appointments for the learner (should respect the existing one)
        $this->plannerService->scheduleForLearner($learner, $weekStart);

        // Assert: Verify that the total number of appointments is 3 (1 existing + 2 new) and total minutes is 180
        $totalAppointments = $learner->appointments()->count();
        $totalMinutes = $learner->appointments()->sum('duration_minutes');

        $this->assertEquals(3, $totalAppointments); // 1 existing + 2 new appointments
        $this->assertEquals(180, $totalMinutes); // 60 (existing) + 120 (new) = 180
    }

    public function test_it_prevents_conflicting_appointments_for_learner(): void
    {
        // Create a discipline and a single slot for Monday 9:00 (60 minutes)
        $discipline = Discipline::factory()->create();
        $conflictingSlot = Slot::factory()->for($discipline)->create([
            'duration_minutes' => 60,
            'week_day' => 1, // Monday
            'start_time_hour' => 9,
            'start_time_minute' => 0,
        ]);

        // Create an operator and a learner with a target of 60 weekly minutes
        $operator = Operator::factory()->create();
        $learner = $this->createLearnerWithOperators(['weekly_minutes' => 60], $operator);

        // Attach the slot to both the learner and the operator
        $learner->slots()->attach($conflictingSlot->id);
        $operator->slots()->attach($conflictingSlot->id);

        // Set the start of the week
        $weekStart = Carbon::parse('2025-06-23');

        // Create an existing conflicting appointment for the learner that overlaps the slot
        Appointment::factory()->create([
            'learner_id' => $learner->id,
            'starts_at' => $weekStart->copy()->setTime(9, 30), // Starts within the 9:00-10:00 slot
            'ends_at' => $weekStart->copy()->setTime(10, 30),
            'duration_minutes' => 60,
        ]);

        // Act: Try to schedule the learner (should not create an appointment if it conflicts)
        $this->expectException(WeeklyPlanException::class);
        $this->plannerService->scheduleForLearner($learner, $weekStart);
        $this->expectExceptionCode(WeeklyPlanException::ALL_SLOTS_CONFLICT);

        // Assert: Verify that no *new* appointment was created for the 9:00 slot due to the conflict
        $newAppointments = $learner->appointments()
            ->where('starts_at', $weekStart->copy()->setTime(9, 0)) // Check for appointment at the slot's time
            ->count();

        $this->assertEquals(0, $newAppointments); // The service should not schedule this conflicting slot
    }

    public function test_it_prevents_conflicting_appointments_for_operator(): void
    {
        // Create a discipline and a single slot for Monday 9:00 (60 minutes)
        $discipline = Discipline::factory()->create();
        $slot = Slot::factory()->for($discipline)->create([
            'duration_minutes' => 60,
            'week_day' => 1, // Monday
            'start_time_hour' => 9,
            'start_time_minute' => 0,
        ]);

        // Create one operator and two learners, both associated with this operator
        $operator = Operator::factory()->create();
        $learner1 = $this->createLearnerWithOperators(['weekly_minutes' => 60], $operator);
        $learner2 = $this->createLearnerWithOperators(['weekly_minutes' => 60], $operator);

        // Both learners want the same single slot, and the operator offers it
        $learner1->slots()->attach($slot->id);
        $learner2->slots()->attach($slot->id);
        $operator->slots()->attach($slot->id);

        // Set the start of the week
        $weekStart = Carbon::parse('2025-06-23');

        // Schedule the first learner for the slot
        $this->plannerService->scheduleForLearner($learner1, $weekStart);

        // Act: Try to schedule the second learner (should fail as the slot is now occupied by learner1)
        $this->expectException(WeeklyPlanException::class);
        $this->plannerService->scheduleForLearner($learner2, $weekStart);
        $this->expectExceptionCode(WeeklyPlanException::NO_AVAILABLE_SLOTS);

        // Assert: Verify that the operator only has one appointment at that specific time (no conflicts)
        $operatorAppointments = Appointment::where('operator_id', $operator->id)
            ->where('starts_at', $weekStart->copy()->setTime(9, 0))
            ->count();

        $this->assertEquals(1, $operatorAppointments); // Only one appointment should be scheduled for the operator at this time
    }

    public function test_it_handles_zero_weekly_minutes_learner(): void
    {
        // Create an operator and a learner with a weekly minute target of 0
        $operator = Operator::factory()->create();
        $learner = $this->createLearnerWithOperators(['weekly_minutes' => 0], $operator);

        // Set the start of the week
        $weekStart = Carbon::parse('2025-06-23');

        // Act: Try to schedule the learner
        $this->expectException(WeeklyPlanException::class);
        $this->plannerService->scheduleForLearner($learner, $weekStart);
        $this->expectExceptionCode(WeeklyPlanException::NO_WEEKLY_MINUTES);

        // Assert: No appointments should be created for a learner with a 0-minute target
        $this->assertEquals(0, $learner->appointments()->count());
    }

    public function test_it_handles_learner_without_operator(): void
    {
        // Create a learner without an associated operator
        $learner = Learner::factory()->create(['weekly_minutes' => 180]);
        // Set the start of the week
        $weekStart = Carbon::parse('2025-06-23');

        // Act: Try to schedule the learner
        $this->expectException(WeeklyPlanException::class);
        $this->plannerService->scheduleForLearner($learner, $weekStart);
        $this->expectExceptionCode(WeeklyPlanException::NO_OPERATOR);

        // Assert: No appointments should be created if the learner has no assigned operator
        $this->assertEquals(0, $learner->appointments()->count());
    }

    public function test_it_handles_no_available_slots_scenario(): void
    {
        // Create an operator and a learner associated with them
        $operator = Operator::factory()->create();
        $learner = $this->createLearnerWithOperators(['weekly_minutes' => 180], $operator);

        // Set the start of the week
        $weekStart = Carbon::parse('2025-06-23');

        // Act: Try to schedule the learner with no slots attached to either learner or operator
        $this->expectException(WeeklyPlanException::class);
        $this->plannerService->scheduleForLearner($learner, $weekStart);
        $this->expectExceptionCode(WeeklyPlanException::NO_AVAILABLE_SLOTS);

        // Assert: No appointments should be created if there are no available slots
        $this->assertEquals(0, $learner->appointments()->count());
    }

    public function test_it_adjusts_non_monday_start_date(): void
    {
        // Create a discipline and a slot for Monday (week_day 1)
        $discipline = Discipline::factory()->create();
        $slot = Slot::factory()->for($discipline)->create([
            'duration_minutes' => 60,
            'week_day' => 1, // Monday
            'start_time_hour' => 9,
            'start_time_minute' => 0,
        ]);

        // Create an operator and a learner
        $operator = Operator::factory()->create();
        $learner = $this->createLearnerWithOperators(['weekly_minutes' => 60], $operator);

        // Attach the Monday slot to both the learner and the operator
        $learner->slots()->attach($slot->id);
        $operator->slots()->attach($slot->id);

        // Act: Pass a Wednesday date instead of a Monday to the scheduler
        $wednesday = Carbon::parse('2025-06-25'); // Wednesday, June 25, 2025
        $this->plannerService->scheduleForLearner($learner, $wednesday);

        // Assert: The appointment should still be scheduled for the Monday of that week (June 23, 2025)
        $appointment = $learner->appointments()->first();
        $this->assertNotNull($appointment);
        $this->assertEquals('2025-06-23', $appointment->starts_at->format('Y-m-d')); // Verify it's scheduled for Monday
    }

    public function test_it_provides_accurate_scheduling_summary(): void
    {
        // Create a discipline and a 90-minute slot for Monday
        $discipline = Discipline::factory()->create();
        $slot = Slot::factory()->for($discipline)->create([
            'duration_minutes' => 90,
            'week_day' => 1,
            'start_time_hour' => 9,
            'start_time_minute' => 0,
        ]);

        // Create an operator and a learner with a target of 180 weekly minutes
        $operator = Operator::factory()->create();
        $learner = $this->createLearnerWithOperators(['weekly_minutes' => 180], $operator);

        // Attach the slot to both the learner and the operator
        $learner->slots()->attach($slot->id);
        $operator->slots()->attach($slot->id);

        // Set the start of the week
        $weekStart = Carbon::parse('2025-06-23');

        // Act: Schedule the learner and retrieve the scheduling summary
        $this->plannerService->scheduleForLearner($learner, $weekStart);
        $summary = $this->plannerService->getSchedulingSummary($learner, $weekStart);

        // Assert: Verify all fields in the scheduling summary are accurate
        $this->assertEquals($learner->id, $summary['learner_id']);
        $this->assertEquals('2025-06-23', $summary['week_start']);
        $this->assertEquals(180, $summary['weekly_minutes_target']); // Target minutes
        $this->assertEquals(90, $summary['scheduled_minutes']);     // Only 90 minutes could be scheduled
        $this->assertEquals(90, $summary['remaining_minutes']);     // 180 - 90 = 90 remaining
        $this->assertEquals(1, $summary['appointments_count']);    // Only one appointment created
        $this->assertEquals(50.0, $summary['completion_percentage']); // 90/180 = 50%
    }

    public function test_it_schedules_multiple_learners_without_conflicts(): void
    {
        // Create a discipline and four 60-minute slots (total 240 minutes) across Monday and Tuesday
        $discipline = Discipline::factory()->create();
        $slots = Slot::factory()->for($discipline)->count(4)->sequence(
            ['duration_minutes' => 60, 'week_day' => 1, 'start_time_hour' => 9, 'start_time_minute' => 0],  // Mon 9:00
            ['duration_minutes' => 60, 'week_day' => 1, 'start_time_hour' => 10, 'start_time_minute' => 0], // Mon 10:00
            ['duration_minutes' => 60, 'week_day' => 2, 'start_time_hour' => 9, 'start_time_minute' => 0],  // Tue 9:00
            ['duration_minutes' => 60, 'week_day' => 2, 'start_time_hour' => 10, 'start_time_minute' => 0], // Tue 10:00
        )->create();

        // Create one operator and two learners, both with a target of 120 weekly minutes
        $operator = Operator::factory()->create();
        $learner1 = $this->createLearnerWithOperators(['weekly_minutes' => 120], $operator);
        $learner2 = $this->createLearnerWithOperators(['weekly_minutes' => 120], $operator);

        // Both learners and the operator have access to all four slots
        foreach ($slots as $slot) {
            $learner1->slots()->attach($slot->id);
            $learner2->slots()->attach($slot->id);
            $operator->slots()->attach($slot->id);
        }

        // Set the start of the week
        $weekStart = Carbon::parse('2025-06-23');

        // Act: Schedule both learners sequentially
        $this->plannerService->scheduleForLearner($learner1, $weekStart);
        $this->plannerService->scheduleForLearner($learner2, $weekStart);

        // Assert: Verify total appointments for the operator (should be 4, as slots are shared fairly)
        $totalAppointments = Appointment::where('operator_id', $operator->id)->count();
        $this->assertEquals(4, $totalAppointments);

        // Assert: Each learner should have 2 appointments (120 minutes each)
        $this->assertEquals(2, $learner1->appointments()->count());
        $this->assertEquals(2, $learner2->appointments()->count());

        // Assert: Crucially, ensure there are no time conflicts among appointments for the operator
        $appointmentTimes = Appointment::where('operator_id', $operator->id)
            ->pluck('starts_at')
            ->toArray();
        // The count of unique start times should equal the total count of appointments
        $this->assertEquals(count($appointmentTimes), count(array_unique($appointmentTimes)));
    }

    public function test_it_enforces_one_appointment_per_day_for_learner(): void
    {
        // Create a discipline and multiple slots (many on Mon and Tue, one on Wed)
        $discipline = Discipline::factory()->create();
        $slots = Slot::factory()->for($discipline)->count(7)->sequence(
            ['duration_minutes' => 60, 'week_day' => 1, 'start_time_hour' => 9,  'start_time_minute' => 0], // Mon 09:00
            ['duration_minutes' => 60, 'week_day' => 1, 'start_time_hour' => 10, 'start_time_minute' => 0], // Mon 10:00
            ['duration_minutes' => 60, 'week_day' => 1, 'start_time_hour' => 11, 'start_time_minute' => 0], // Mon 11:00
            ['duration_minutes' => 60, 'week_day' => 2, 'start_time_hour' => 9,  'start_time_minute' => 0], // Tue 09:00
            ['duration_minutes' => 60, 'week_day' => 2, 'start_time_hour' => 10, 'start_time_minute' => 0], // Tue 10:00
            ['duration_minutes' => 60, 'week_day' => 2, 'start_time_hour' => 11, 'start_time_minute' => 0], // Tue 11:00
            ['duration_minutes' => 60, 'week_day' => 3, 'start_time_hour' => 9,  'start_time_minute' => 0], // Wed 09:00
        )->create();

        // Create operator and learner. Learner needs 180 minutes (3 sessions)
        $operator = Operator::factory()->create();
        $learner = $this->createLearnerWithOperators(['weekly_minutes' => 180], $operator);

        // Attach all slots to both learner and operator
        foreach ($slots as $slot) {
            $learner->slots()->attach($slot->id);
            $operator->slots()->attach($slot->id);
        }

        // Schedule for the week
        $weekStart = Carbon::parse('2025-06-23'); // Monday
        $this->plannerService->scheduleForLearner($learner, $weekStart);

        // Fetch created appointments
        $appointments = $learner->appointments()->orderBy('starts_at')->get();

        // We expect 3 appointments (3 x 60 = 180)
        $this->assertCount(3, $appointments);
        $this->assertEquals(180, $appointments->sum('duration_minutes'));

        // Ensure appointments fall on distinct days (max 1 appointment per day)
        $days = $appointments->map(fn($a) => $a->starts_at->dayOfWeekIso)->toArray();
        $uniqueDays = array_unique($days);

        $this->assertCount(3, $uniqueDays, 'Appointments should be on 3 distinct days (max 1 per day).');

        // Extra: assert no day has >1 appointment
        $grouped = $appointments->groupBy(fn($a) => $a->starts_at->dayOfWeekIso);
        foreach ($grouped as $day => $apptsOnDay) {
            $this->assertLessThanOrEqual(1, $apptsOnDay->count(), "More than one appointment scheduled on day {$day}");
        }
    }
}

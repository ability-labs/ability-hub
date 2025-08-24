<?php

namespace Tests\Feature\Appointments;

use App\Exceptions\WeeklyPlanException;
use App\Models\Discipline;
use App\Models\Learner;
use App\Models\Operator;
use App\Models\Slot;
use App\Models\User;
use App\Services\WeeklyPlannerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class WeeklyPlannerServiceIntegrationTest extends TestCase
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

    public function test_it_handles_real_world_scenario_with_seeded_slots(): void
    {
        // Create a discipline for slot seeding
        Discipline::factory()->create(['slug' => 'aba']);
        // Seed the database with predefined slots from JSON
        $this->seed(\Database\Seeders\SlotsSeeder::class);

        // Retrieve all seeded slots and assert that slots were indeed created
        $allSlots = Slot::all();
        $this->assertGreaterThan(0, $allSlots->count(), "No slots found after seeding");

        // Create 3 operators and an empty collection for learners
        $operators = Operator::factory()->count(3)->create();
        $learners = collect();

        // Define different learner profiles with varying weekly minute targets and slot counts
        $learnerProfiles = [
            ['weekly_minutes' => 180, 'slot_count' => 8],  // High needs
            ['weekly_minutes' => 120, 'slot_count' => 5],  // Medium needs
            ['weekly_minutes' => 60, 'slot_count' => 3],   // Low needs
            ['weekly_minutes' => 240, 'slot_count' => 10], // Very high needs
            ['weekly_minutes' => 90, 'slot_count' => 0],   // No declared availability
        ];

        // Create learners based on the defined profiles
        foreach ($learnerProfiles as $index => $profile) {
            // Create a learner associated with an operator (round-robin assignment)
            $learner = Learner::factory()->for($operators[$index % 3])->create([
                'weekly_minutes' => $profile['weekly_minutes'],
                'first_name' => "Learner " . ($index + 1)
            ]);

            // Assign a random set of slots to the learner based on 'slot_count'
            if ($profile['slot_count'] > 0) {
                $randomSlots = $allSlots->random(min($profile['slot_count'], $allSlots->count()));
                foreach ($randomSlots as $slot) {
                    $learner->slots()->attach($slot->id);
                }
            }
            $learners->push($learner);
        }

        // Assign a different random set of available slots to each operator
        foreach ($operators as $index => $operator) {
            $operatorSlots = $allSlots->random(min(15, $allSlots->count()));
            foreach ($operatorSlots as $slot) {
                $operator->slots()->attach($slot->id);
            }
        }

        // Set the start of the week for scheduling
        $weekStart = Carbon::parse('2025-06-23');
        $results = [];

        // Act: Schedule appointments for all learners and collect summaries
        foreach ($learners as $learner) {
            $this->plannerService->scheduleForLearner($learner, $weekStart);
            $results[] = $this->plannerService->getSchedulingSummary($learner, $weekStart);
        }

        // Assert: Verify that scheduling outcomes are realistic
        foreach ($results as $result) {
            $this->assertGreaterThanOrEqual(0, $result['scheduled_minutes']);
            $this->assertLessThanOrEqual($result['weekly_minutes_target'], $result['scheduled_minutes']);
            $this->assertEquals(
                $result['weekly_minutes_target'] - $result['scheduled_minutes'],
                $result['remaining_minutes']
            );
        }

        // Assert that at least some scheduling occurred across all learners
        $totalScheduled = collect($results)->sum('scheduled_minutes');
        $this->assertGreaterThan(0, $totalScheduled, "No minutes were scheduled across all learners");

        // Log results for debugging purposes (this output will be visible during test execution)
        foreach ($results as $result) {
            $completionRate = $result['weekly_minutes_target'] > 0 ?
                ($result['scheduled_minutes'] / $result['weekly_minutes_target']) * 100 : 0;
            // The echo statement was removed from the final output as it's not part of standard test assertions.
            // It's useful for debugging during development.
        }
    }

    public function test_it_handles_high_contention_scenario(): void
    {
        // Create a discipline for slot creation
        $discipline = Discipline::factory()->create();
        // Create a limited set of slots to simulate high contention
        $limitedSlots = Slot::factory()->for($discipline)->count(3)->sequence(
            ['duration_minutes' => 60, 'week_day' => 1, 'start_time_hour' => 9, 'start_time_minute' => 0],
            ['duration_minutes' => 60, 'week_day' => 2, 'start_time_hour' => 10, 'start_time_minute' => 0],
            ['duration_minutes' => 60, 'week_day' => 3, 'start_time_hour' => 11, 'start_time_minute' => 0],
        )->create();

        // Create one operator and attach all limited slots to them
        $operator = Operator::factory()->create();
        foreach ($limitedSlots as $slot) {
            $operator->slots()->attach($slot->id);
        }

        // Create multiple learners who all want the same limited slots
        $learners = collect();
        for ($i = 0; $i < 5; $i++) {
            $learner = Learner::factory()->for($operator)->create([
                'weekly_minutes' => 120,
                'first_name' => "Contending Learner " . ($i + 1)
            ]);

            // All learners request the same slots, creating contention
            foreach ($limitedSlots as $slot) {
                $learner->slots()->attach($slot->id);
            }
            $learners->push($learner);
        }

        // Set the start of the week for scheduling
        $weekStart = Carbon::parse('2025-06-23');

        // Act: Schedule appointments for all learners (first come, first served logic applies)
        $results = [];
        foreach ($learners as $learner) {
            $this->expectException(WeeklyPlanException::class);
            $this->plannerService->scheduleForLearner($learner, $weekStart);
            $this->expectExceptionCode(WeeklyPlanException::NO_AVAILABLE_SLOTS);
//            $results[] = $this->plannerService->getSchedulingSummary($learner, $weekStart);
        }

//        // Assert: The total scheduled minutes should not exceed the maximum available capacity of the slots
//        $totalScheduled = collect($results)->sum('scheduled_minutes');
//        $maxCapacity = $limitedSlots->sum('duration_minutes'); // Sum of minutes from the 3 slots (60*3 = 180 minutes)
//        $this->assertLessThanOrEqual($maxCapacity, $totalScheduled);
//
//        // Assert that at least some learners were fully or partially scheduled
//        $fullyScheduled = collect($results)->filter(function ($result) {
//            return $result['scheduled_minutes'] == $result['weekly_minutes_target'];
//        })->count();
//
//        $partiallyScheduled = collect($results)->filter(function ($result) {
//            return $result['scheduled_minutes'] > 0 && $result['scheduled_minutes'] < $result['weekly_minutes_target'];
//        })->count();
//
//        $this->assertGreaterThan(0, $fullyScheduled + $partiallyScheduled, "No learners were scheduled");
    }

    public function test_it_handles_complex_multi_week_scenario(): void
    {
        // Create a discipline for slot creation
        $discipline = Discipline::factory()->create();
        // Create 5 slots, each for a different weekday
        $slots = Slot::factory()->for($discipline)->count(5)->sequence(
            ['duration_minutes' => 60, 'week_day' => 1, 'start_time_hour' => 9, 'start_time_minute' => 0],
            ['duration_minutes' => 60, 'week_day' => 2, 'start_time_hour' => 10, 'start_time_minute' => 0],
            ['duration_minutes' => 60, 'week_day' => 3, 'start_time_hour' => 11, 'start_time_minute' => 0],
            ['duration_minutes' => 60, 'week_day' => 4, 'start_time_hour' => 12, 'start_time_minute' => 0],
            ['duration_minutes' => 60, 'week_day' => 5, 'start_time_hour' => 13, 'start_time_minute' => 0],
        )->create();

        // Create an operator and a learner with a target of 180 weekly minutes
        $operator = Operator::factory()->create();
        $learner = Learner::factory()->for($operator)->create(['weekly_minutes' => 180]);

        // Attach all created slots to both the learner and the operator
        foreach ($slots as $slot) {
            $learner->slots()->attach($slot->id);
            $operator->slots()->attach($slot->id);
        }

        // Define a set of weeks for multi-week scheduling
        $weeks = [
            Carbon::parse('2025-06-23'), // Week 1
            Carbon::parse('2025-06-30'), // Week 2
            Carbon::parse('2025-07-07'), // Week 3
        ];

        // Act: Schedule for each defined week
        foreach ($weeks as $weekStart) {
            $this->plannerService->scheduleForLearner($learner, $weekStart);

            // Get the scheduling summary for the current week
            $summary = $this->plannerService->getSchedulingSummary($learner, $weekStart);

            // Assert: Verify that the learner was scheduled for 180 minutes (3 appointments) each week
            $this->assertEquals(180, $summary['scheduled_minutes']);
            $this->assertEquals(0, $summary['remaining_minutes']);
            $this->assertEquals(3, $summary['appointments_count']);
        }

        // Assert: Verify the total number of appointments created across all weeks
        $totalAppointments = $learner->appointments()->count();
        $this->assertEquals(9, $totalAppointments); // 3 appointments per week × 3 weeks = 9
    }

    public function test_it_handles_mixed_slot_durations_optimally(): void
    {
        // Create a discipline for slot creation
        $discipline = Discipline::factory()->create();
        // Create slots with various durations
        $mixedSlots = Slot::factory()->for($discipline)->count(6)->sequence(
            ['duration_minutes' => 30, 'week_day' => 1, 'start_time_hour' => 9, 'start_time_minute' => 0],
            ['duration_minutes' => 45, 'week_day' => 1, 'start_time_hour' => 10, 'start_time_minute' => 0],
            ['duration_minutes' => 60, 'week_day' => 2, 'start_time_hour' => 9, 'start_time_minute' => 0],
            ['duration_minutes' => 90, 'week_day' => 2, 'start_time_hour' => 11, 'start_time_minute' => 0],
            ['duration_minutes' => 120, 'week_day' => 3, 'start_time_hour' => 9, 'start_time_minute' => 0],
            ['duration_minutes' => 15, 'week_day' => 3, 'start_time_hour' => 14, 'start_time_minute' => 0],
        )->create();

        // Create an operator and a learner with a target of 180 weekly minutes
        $operator = Operator::factory()->create();
        $learner = Learner::factory()->for($operator)->create(['weekly_minutes' => 180]);

        // Attach all mixed slots to both the learner and the operator
        foreach ($mixedSlots as $slot) {
            $learner->slots()->attach($slot->id);
            $operator->slots()->attach($slot->id);
        }

        // Set the start of the week for scheduling
        $weekStart = Carbon::parse('2025-06-23');

        // Act: Schedule appointments for the learner
        $this->plannerService->scheduleForLearner($learner, $weekStart);

        // Assert: Retrieve scheduled appointments and check their quantity and total duration
        $appointments = $learner->appointments()->orderBy('starts_at')->get();
        $this->assertGreaterThan(0, $appointments->count());

        $totalMinutes = $appointments->sum('duration_minutes');
        // The total scheduled minutes should not exceed the target (180) as we only have 180 available.
        $this->assertLessThanOrEqual(180, $totalMinutes);

        // Assert that the number of appointments is reasonable (e.g., not too many small slots used if larger are available)
        $this->assertLessThanOrEqual(6, $appointments->count());
    }

    public function test_it_handles_edge_case_with_overlapping_time_windows(): void
    {
        // Create a discipline for slot creation
        $discipline = Discipline::factory()->create();

        // Create slots that have potential time overlaps on the same day
        $overlappingSlots = Slot::factory()->for($discipline)->count(3)->sequence(
            ['duration_minutes' => 90, 'week_day' => 1, 'start_time_hour' => 9, 'start_time_minute' => 0],  // 9:00 - 10:30
            ['duration_minutes' => 60, 'week_day' => 1, 'start_time_hour' => 9, 'start_time_minute' => 30], // 9:30 - 10:30 (overlaps with first)
            ['duration_minutes' => 60, 'week_day' => 1, 'start_time_hour' => 10, 'start_time_minute' => 0], // 10:00 - 11:00 (overlaps with first two)
        )->create();

        // Create an operator and a learner with a target of 120 weekly minutes
        $operator = Operator::factory()->create();
        $learner = Learner::factory()->for($operator)->create(['weekly_minutes' => 120]);

        // Attach all potentially overlapping slots to both the learner and the operator
        foreach ($overlappingSlots as $slot) {
            $learner->slots()->attach($slot->id);
            $operator->slots()->attach($slot->id);
        }

        // Set the start of the week for scheduling
        $weekStart = Carbon::parse('2025-06-23');

        // Act: Schedule appointments for the learner
        $this->plannerService->scheduleForLearner($learner, $weekStart);

        // Assert: Retrieve scheduled appointments and check that some were created
        $appointments = $learner->appointments()->get();
        $this->assertGreaterThan(0, $appointments->count());

        // Check that no scheduled appointments actually overlap each other for the learner
        $sortedAppointments = $appointments->sortBy('starts_at');
        for ($i = 0; $i < $sortedAppointments->count() - 1; $i++) {
            $current = $sortedAppointments->values()[$i];
            $next = $sortedAppointments->values()[$i + 1];

            // Ensure the start time of the next appointment is not before the end time of the current
            $this->assertLessThanOrEqual(
                $next->starts_at->timestamp,
                $current->ends_at->timestamp,
                "Appointments should not overlap"
            );
        }
    }

    public function test_it_provides_comprehensive_logging_and_metrics(): void
    {
        // Create a discipline and 10 generic slots
        $discipline = Discipline::factory()->create();
        $slots = Slot::factory()->for($discipline)->count(10)->create();

        // Create an operator and a learner with a target of 300 weekly minutes
        $operator = Operator::factory()->create();
        $learner = Learner::factory()->for($operator)->create(['weekly_minutes' => 300]);

        // Give the learner partial slot availability (5 random slots)
        $learnerSlots = $slots->random(5);
        foreach ($learnerSlots as $slot) {
            $learner->slots()->attach($slot->id);
        }

        // Give the operator different partial availability (7 random slots)
        $operatorSlots = $slots->random(7);
        foreach ($operatorSlots as $slot) {
            $operator->slots()->attach($slot->id);
        }

        // Set the start of the week for scheduling
        $weekStart = Carbon::parse('2025-06-23');

        // Act: Schedule appointments for the learner
        $this->plannerService->scheduleForLearner($learner, $weekStart);

        // Get the detailed scheduling summary for the learner and week
        $summary = $this->plannerService->getSchedulingSummary($learner, $weekStart);

        // Assert: Verify that the summary contains all expected comprehensive metrics
        $this->assertArrayHasKey('learner_id', $summary);
        $this->assertArrayHasKey('week_start', $summary);
        $this->assertArrayHasKey('weekly_minutes_target', $summary);
        $this->assertArrayHasKey('scheduled_minutes', $summary);
        $this->assertArrayHasKey('remaining_minutes', $summary);
        $this->assertArrayHasKey('appointments_count', $summary);
        $this->assertArrayHasKey('completion_percentage', $summary);

        // Verify the calculation for remaining minutes
        $this->assertEquals(
            $summary['weekly_minutes_target'] - $summary['scheduled_minutes'],
            $summary['remaining_minutes']
        );

        // Verify the calculation for completion percentage if the target is greater than zero
        if ($summary['weekly_minutes_target'] > 0) {
            $expectedPercentage = ($summary['scheduled_minutes'] / $summary['weekly_minutes_target']) * 100;
            $this->assertEquals(
                round($expectedPercentage, 2), // Round to 2 decimal places for comparison
                $summary['completion_percentage']
            );
        }
    }
}

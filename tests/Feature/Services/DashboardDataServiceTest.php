<?php

namespace Tests\Feature\Services;

use App\Models\Appointment;
use App\Models\Discipline;
use App\Models\Learner;
use App\Models\Operator;
use App\Models\User;
use App\Services\DashboardDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardDataServiceTest extends TestCase
{
    use RefreshDatabase;

    private DashboardDataService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DashboardDataService();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_get_stats_returns_correct_counts_and_is_cached()
    {
        Learner::factory()->count(3)->create(['user_id' => $this->user->id]);
        Operator::factory()->count(2)->create(['user_id' => $this->user->id]);
        Appointment::factory()->count(5)->create(['user_id' => $this->user->id]);

        $stats = $this->service->getStats();

        $this->assertEquals(3, $stats['learners']);
        $this->assertEquals(2, $stats['operators']);
        $this->assertEquals(5, $stats['appointments']);

        // Check cache
        $this->assertTrue(Cache::has("dashboard_stats_{$this->user->id}"));
    }

    public function test_get_weekly_appointment_stats_returns_data_for_14_days()
    {
        $today = now()->format('Y-m-d');
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'starts_at' => now(),
            'ends_at' => now()->addHour()
        ]);

        $stats = $this->service->getWeeklyAppointmentStats();

        $this->assertCount(10, $stats);
        if (!now()->isWeekend()) {
            $this->assertEquals(1, $stats[$today]);
        }
    }

    public function test_get_recent_activity_returns_combined_resources()
    {
        $learner = Learner::factory()->create(['user_id' => $this->user->id, 'created_at' => now()->subDay()]);
        $operator = Operator::factory()->create(['user_id' => $this->user->id, 'created_at' => now()]);

        $activity = $this->service->getRecentActivity();

        $this->assertCount(2, $activity);
        $this->assertEquals('operator', $activity[0]['type']);
        $this->assertEquals('learner', $activity[1]['type']);
    }

    public function test_get_discipline_distribution_returns_correct_data()
    {
        $discipline = Discipline::factory()->create(['name' => ['it' => 'Test Discipline']]);
        Appointment::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'discipline_id' => $discipline->id
        ]);

        $dist = $this->service->getDisciplineDistribution();

        $this->assertCount(1, $dist);
        $this->assertEquals('Test Discipline', $dist[0]['discipline']);
        $this->assertEquals(3, $dist[0]['count']);
    }
}

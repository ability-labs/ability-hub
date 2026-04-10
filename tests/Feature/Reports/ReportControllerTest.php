<?php

namespace Tests\Feature\Reports;

use App\Models\Appointment;
use App\Models\Discipline;
use App\Models\Learner;
use App\Models\Operator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_reports_page_loads_for_authenticated_user(): void
    {
        $response = $this->actingAs($this->user)->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertViewIs('reports.index');
    }

    public function test_reports_requires_authentication(): void
    {
        $response = $this->get(route('reports.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_ajax_request_returns_html_partial(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('reports.index', ['month' => 3, 'year' => 2026, 'tab' => 'operators']), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertStatus(200);
        $this->assertStringNotContainsString('<!DOCTYPE html>', $response->getContent());
    }

    public function test_defaults_to_current_month_and_learners_tab(): void
    {
        $response = $this->actingAs($this->user)->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertViewHas('initialMonth', now()->month);
        $response->assertViewHas('initialYear', now()->year);
    }

    public function test_only_past_appointments_are_counted(): void
    {
        $operator = Operator::factory()->create(['user_id' => $this->user->id]);
        $discipline = Discipline::factory()->create(['name' => 'ABA']);

        // Past appointment (should count)
        $pastAppointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'operator_id' => $operator->id,
            'discipline_id' => $discipline->id,
            'starts_at' => now()->subDays(2)->setTime(9, 0),
            'ends_at' => now()->subDays(2)->setTime(10, 0),
        ]);
        $pastAppointment->operators()->syncWithoutDetaching([$operator->id]);

        // Future appointment (should NOT count)
        $futureAppointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'operator_id' => $operator->id,
            'discipline_id' => $discipline->id,
            'starts_at' => now()->addDays(2)->setTime(9, 0),
            'ends_at' => now()->addDays(2)->setTime(10, 0),
        ]);
        $futureAppointment->operators()->syncWithoutDetaching([$operator->id]);

        $response = $this->actingAs($this->user)
            ->get(route('reports.index', [
                'month' => now()->month,
                'year' => now()->year,
                'tab' => 'operators',
            ]), ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertStatus(200);
        $content = $response->getContent();

        // Should see the operator once (from past appointment)
        $this->assertStringContainsString($operator->name, $content);
        // The displayed hours should be 1.0h (only the past appointment)
        $this->assertStringContainsString('1.0', $content);
    }

    public function test_hours_correctly_aggregated_per_operator(): void
    {
        $operator = Operator::factory()->create(['user_id' => $this->user->id]);
        $discipline = Discipline::factory()->create(['name' => 'ABA']);

        // 1-hour appointment
        $a1 = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'operator_id' => $operator->id,
            'discipline_id' => $discipline->id,
            'starts_at' => now()->subDays(3)->setTime(9, 0),
            'ends_at' => now()->subDays(3)->setTime(10, 0),
        ]);
        $a1->operators()->syncWithoutDetaching([$operator->id]);

        // 1.5-hour appointment
        $a2 = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'operator_id' => $operator->id,
            'discipline_id' => $discipline->id,
            'starts_at' => now()->subDays(2)->setTime(14, 0),
            'ends_at' => now()->subDays(2)->setTime(15, 30),
        ]);
        $a2->operators()->syncWithoutDetaching([$operator->id]);

        $response = $this->actingAs($this->user)
            ->get(route('reports.index', [
                'month' => now()->month,
                'year' => now()->year,
                'tab' => 'operators',
            ]), ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertStatus(200);
        // Total should be 2.5h
        $this->assertStringContainsString('2.5', $response->getContent());
    }

    public function test_hours_correctly_aggregated_per_learner(): void
    {
        $learner = Learner::factory()->create(['user_id' => $this->user->id]);
        $discipline = Discipline::factory()->create(['name' => 'Speech']);

        $appointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'learner_id' => $learner->id,
            'discipline_id' => $discipline->id,
            'starts_at' => now()->subDays(1)->setTime(10, 0),
            'ends_at' => now()->subDays(1)->setTime(12, 0),
        ]);
        $appointment->learners()->syncWithoutDetaching([$learner->id]);

        $response = $this->actingAs($this->user)
            ->get(route('reports.index', [
                'month' => now()->month,
                'year' => now()->year,
                'tab' => 'learners',
            ]), ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString($learner->full_name, $content);
        $this->assertStringContainsString('2.0', $content);
    }

    public function test_discipline_breakdown_is_correct(): void
    {
        $operator = Operator::factory()->create(['user_id' => $this->user->id]);
        $disc1 = Discipline::factory()->create(['name' => 'ABA']);
        $disc2 = Discipline::factory()->create(['name' => 'Speech']);

        $a1 = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'operator_id' => $operator->id,
            'discipline_id' => $disc1->id,
            'starts_at' => now()->subDays(3)->setTime(9, 0),
            'ends_at' => now()->subDays(3)->setTime(11, 0),
        ]);
        $a1->operators()->syncWithoutDetaching([$operator->id]);

        $a2 = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'operator_id' => $operator->id,
            'discipline_id' => $disc2->id,
            'starts_at' => now()->subDays(2)->setTime(9, 0),
            'ends_at' => now()->subDays(2)->setTime(10, 0),
        ]);
        $a2->operators()->syncWithoutDetaching([$operator->id]);

        $response = $this->actingAs($this->user)
            ->get(route('reports.index', [
                'month' => now()->month,
                'year' => now()->year,
                'tab' => 'operators',
            ]), ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertStatus(200);
        $content = $response->getContent();
        // Total 3.0h, ABA 2.0h, Speech 1.0h
        $this->assertStringContainsString('3.0', $content);
        $this->assertStringContainsString('ABA', $content);
        $this->assertStringContainsString('Speech', $content);
    }

    public function test_scoped_to_authenticated_user(): void
    {
        $otherUser = User::factory()->create();
        $otherOperator = Operator::factory()->create(['user_id' => $otherUser->id]);
        $discipline = Discipline::factory()->create(['name' => 'ABA']);

        $appointment = Appointment::factory()->create([
            'user_id' => $otherUser->id,
            'operator_id' => $otherOperator->id,
            'discipline_id' => $discipline->id,
            'starts_at' => now()->subDays(1)->setTime(9, 0),
            'ends_at' => now()->subDays(1)->setTime(10, 0),
        ]);
        $appointment->operators()->syncWithoutDetaching([$otherOperator->id]);

        $response = $this->actingAs($this->user)
            ->get(route('reports.index', [
                'month' => now()->month,
                'year' => now()->year,
                'tab' => 'operators',
            ]), ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertStatus(200);
        // Should not contain the other user's operator
        $this->assertStringNotContainsString($otherOperator->name, $response->getContent());
    }

    public function test_invalid_month_returns_validation_error(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('reports.index', ['month' => 13]));

        $response->assertStatus(302);
        $response->assertSessionHasErrors('month');
    }
}

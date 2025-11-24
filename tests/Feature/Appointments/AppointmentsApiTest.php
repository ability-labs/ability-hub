<?php

namespace Tests\Feature\Appointments;

use App\Models\Appointment;
use App\Models\Discipline;
use App\Models\Learner;
use App\Models\Operator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AppointmentsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_appointments_for_given_range(): void
    {
        $user = User::factory()->create();
        $discipline = Discipline::factory()->create();
        $operator = Operator::factory()->for($user)->create();
        $learner = Learner::factory()->for($user)->create();

        $inRangeStart = Carbon::parse('2024-05-07 09:00:00');
        $inRangeEnd = Carbon::parse('2024-05-07 10:30:00');

        $appointmentInRange = Appointment::factory()
            ->for($user)
            ->for($learner)
            ->for($operator)
            ->for($discipline)
            ->create([
                'starts_at' => $inRangeStart,
                'ends_at' => $inRangeEnd,
            ]);

        Appointment::factory()
            ->for($user)
            ->for($learner)
            ->for($operator)
            ->for($discipline)
            ->create([
                'starts_at' => Carbon::parse('2024-06-15 09:00:00'),
                'ends_at' => Carbon::parse('2024-06-15 10:00:00'),
            ]);

        $response = $this->actingAs($user)
            ->getJson(route('api.appointments.index', [
                'starts_at' => '2024-05-01',
                'ends_at' => '2024-05-31',
            ]));

        $response->assertOk();
        $response->assertJsonCount(1, 'appointments');
        $response->assertJsonFragment(['id' => $appointmentInRange->id]);
        $response->assertJsonPath('appointments.0.start', $inRangeStart->toJSON());
        $response->assertJsonPath('appointments.0.end', $inRangeEnd->toJSON());
        $response->assertJsonStructure([
            'appointments' => [['id', 'start', 'end', 'extendedProps']],
            'operators',
            'learners',
            'disciplines',
            'range' => ['starts_at', 'ends_at'],
        ]);
    }
}

<?php

namespace Tests\Feature\Actions\Appointments;

use App\Actions\Appointments\DuplicateWeeklyAppointments;
use App\Models\Appointment;
use App\Models\Learner;
use App\Models\Operator;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DuplicateWeeklyAppointmentsTest extends TestCase
{
    public function test_it_duplicates_appointments_for_a_specific_week_and_user_only()
    {
        // 1. Arrange
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        
        // Define the source week (e.g., first week of Jan 2024)
        $weekStart = Carbon::parse('2024-01-01'); // Monday
        $weekEnd = Carbon::parse('2024-01-07');   // Sunday
        
        // Target week will be the following week
        $targetWeekStart = $weekStart->copy()->addWeek();
        
        // Create appointments inside the source week for the user
        $appt1 = Appointment::factory()->create([
            'user_id' => $user->id,
            'starts_at' => $weekStart->copy()->setTime(10, 0), // Mon 10:00
            'ends_at' => $weekStart->copy()->setTime(11, 0),
        ]);
        
        $appt2 = Appointment::factory()->create([
            'user_id' => $user->id,
            'starts_at' => $weekStart->copy()->addDays(2)->setTime(14, 0), // Wed 14:00
            'ends_at' => $weekStart->copy()->addDays(2)->setTime(15, 0),
        ]);
        
        // Create an appointment BEFORE the source week (should NOT be duplicated)
        $apptPast = Appointment::factory()->create([
            'user_id' => $user->id,
            'starts_at' => $weekStart->copy()->subWeek()->setTime(10, 0),
            'ends_at' => $weekStart->copy()->subWeek()->setTime(11, 0),
        ]);
        
        // Create an appointment AFTER the source week (should NOT be duplicated)
        // We put it 2 weeks ahead just to be safe it's not confused with the target week either, 
        // effectively "out of source range".
        $apptFuture = Appointment::factory()->create([
            'user_id' => $user->id,
            'starts_at' => $weekStart->copy()->addWeeks(2)->setTime(10, 0),
            'ends_at' => $weekStart->copy()->addWeeks(2)->setTime(11, 0),
        ]);
        
        // Create an appointment for ANOTHER user in the source week (should NOT be duplicated)
        $apptOtherUser = Appointment::factory()->create([
            'user_id' => $otherUser->id,
            'starts_at' => $weekStart->copy()->setTime(12, 0),
            'ends_at' => $weekStart->copy()->setTime(13, 0),
        ]);

        // 2. Act
        $action = new DuplicateWeeklyAppointments();
        $count = $action->execute($user, $weekStart, $weekEnd);

        // 3. Assert
        $this->assertEquals(2, $count, 'Should duplicate exactly 2 appointments.');
        
        // Verify total appointments in the system
        // 5 original + 2 duplicates = 7
        $this->assertDatabaseCount('appointments', 7);
        
        // Verify duplicates exist in the target week
        $duplicates = Appointment::where('user_id', $user->id)
            ->whereBetween('starts_at', [
                $targetWeekStart->copy()->startOfDay(),
                $targetWeekStart->copy()->endOfWeek()->endOfDay()
            ])
            ->get();
            
        $this->assertCount(2, $duplicates, 'Should have 2 appointments in the target week.');
        
        // Verify Appt1 duplicate (Mon 10:00 -> Next Mon 10:00)
        $dup1 = $duplicates->first(function ($a) use ($appt1) {
            return $a->operator_id == $appt1->operator_id 
                && $a->learner_id == $appt1->learner_id
                && $a->starts_at->format('H:i') === $appt1->starts_at->format('H:i')
                && $a->starts_at->isSameDay($appt1->starts_at->copy()->addWeek());
        });
        
        $this->assertNotNull($dup1, 'Duplicate of Appt1 not found correctly.');
        $this->assertEquals($appt1->duration_minutes, $dup1->duration_minutes);
        
        // Verify Appt2 duplicate (Wed 14:00 -> Next Wed 14:00)
        $dup2 = $duplicates->first(function ($a) use ($appt2) {
            return $a->operator_id == $appt2->operator_id 
                && $a->learner_id == $appt2->learner_id
                && $a->starts_at->format('H:i') === $appt2->starts_at->format('H:i')
                && $a->starts_at->isSameDay($appt2->starts_at->copy()->addWeek());
        });
        
        $this->assertNotNull($dup2, 'Duplicate of Appt2 not found correctly.');
        
        // Ensure the "Other User" appointment was NOT duplicated
        $otherUserAppointments = Appointment::where('user_id', $otherUser->id)->count();
        $this->assertEquals(1, $otherUserAppointments, 'Other user appointments should not change.');
    }
}

<?php

namespace App\Actions\Appointments;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Carbon;

class DuplicateWeeklyAppointments
{
    public function execute(User $user, Carbon $weekStart, Carbon $weekEnd): int
    {
        $start = $weekStart->copy()->startOfDay();
        $end = $weekEnd->copy()->endOfDay();

        $appointments = Appointment::query()
            ->with(['learners', 'operators'])
            ->where('user_id', $user->id)
            ->whereBetween('starts_at', [$start, $end])
            ->get();

        $created = 0;

        foreach ($appointments as $appointment) {
            $duplicate = $appointment->replicate();

            $duplicate->starts_at = $this->shiftDateByWeek($appointment->starts_at);
            $duplicate->ends_at = $this->shiftDateByWeek($appointment->ends_at);

            $duplicate->save();
            
            $duplicate->learners()->sync($appointment->learners->pluck('id'));
            $duplicate->operators()->sync($appointment->operators->pluck('id'));

            $created++;
        }

        return $created;
    }

    protected function shiftDateByWeek(?Carbon $date): ?Carbon
    {
        if (! $date) {
            return null;
        }

        return $date
            ->copy()
            ->addDays(7)
            ->setTimeFromTimeString($date->format('H:i:s'));
    }
}

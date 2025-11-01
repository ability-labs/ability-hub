<?php

namespace App\Actions\Appointments;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Carbon;

class ClearWeeklyAppointments
{
    public function execute(User $user, Carbon $weekStart): int
    {
        $start = $weekStart->copy()->startOfDay();
        $end = $weekStart->copy()->addDays(5)->endOfDay();

        return Appointment::query()
            ->where('user_id', $user->id)
            ->whereBetween('starts_at', [$start, $end])
            ->delete();
    }
}

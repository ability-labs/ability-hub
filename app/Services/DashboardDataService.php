<?php

namespace App\Services;

use App\Models\Discipline;
use Illuminate\Support\Facades\Auth;

class DashboardDataService
{
    public function getStats(): array
    {
        $user = Auth::user()
            ->loadCount(['learners', 'operators', 'appointments']);
        return [
            'learners' => $user->learners_count,
            'operators' => $user->operators_count,
            'appointments' => $user->appointments_count,
        ];
    }

    public function incomingEvents()
    {
        return Auth::user()
            ->appointments()
            ->whereDate('starts_at', '>', now())
            ->take(5)
            ->get();
    }

    public function getOperatorDisciplines()
    {
        return Auth::user()
            ->operators()
            ->with('disciplines')
            ->get()
            ->pluck('disciplines')
            ->flatten()
            ->unique('id');
    }
}

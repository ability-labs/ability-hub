<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Discipline;
use App\Models\Learner;
use App\Models\Operator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardDataService
{
    private const CACHE_TTL = 3600; // 1 hour

    public function getStats(): array
    {
        $userId = Auth::id();
        return Cache::remember("dashboard_stats_{$userId}", self::CACHE_TTL, function () {
            $user = Auth::user()
                ->loadCount(['learners', 'operators', 'appointments']);

            return [
                'learners' => $user->learners_count,
                'operators' => $user->operators_count,
                'appointments' => $user->appointments_count,
            ];
        });
    }

    public function incomingEvents()
    {
        // Don't cache this as it needs to be real-time
        return Auth::user()
            ->appointments()
            ->with(['learner', 'operator'])
            ->where('starts_at', '>', now())
            ->orderBy('starts_at', 'asc')
            ->take(5)
            ->get();
    }

    public function getOperatorDisciplines()
    {
        $userId = Auth::id();
        return Cache::remember("dashboard_disciplines_{$userId}", self::CACHE_TTL, function () {
            return Auth::user()
                ->operators()
                ->with('disciplines')
                ->get()
                ->pluck('disciplines')
                ->flatten()
                ->unique('id');
        });
    }

    public function getWeeklyAppointmentStats(): array
    {
        $userId = Auth::id();
        return Cache::remember("dashboard_weekly_appointments_{$userId}", self::CACHE_TTL, function () {
            $start = now()->subDays(13)->startOfDay();
            $end = now()->endOfDay();

            $appointments = Auth::user()
                ->appointments()
                ->whereBetween('starts_at', [$start, $end])
                ->selectRaw('DATE(starts_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->pluck('count', 'date');

            $data = [];
            for ($i = 0; $i < 14; $i++) {
                $day = now()->subDays(13 - $i);
                // Saltiamo sabato e domenica
                if ($day->isWeekend()) {
                    continue;
                }
                $date = $day->format('Y-m-d');
                $data[$date] = $appointments->get($date, 0);
            }

            return $data;
        });
    }

    public function getDisciplineDistribution(): array
    {
        $userId = Auth::id();
        $locale = app()->getLocale();
        return Cache::remember("dashboard_discipline_dist_{$userId}", self::CACHE_TTL, function () use ($locale) {
            return \App\Models\Discipline::withCount(['appointments' => function($query) {
                $query->where('user_id', Auth::id());
            }])
            ->get()
            ->map(fn($d) => [
                'discipline' => $d->getTranslation('name', $locale),
                'count' => $d->appointments_count,
                'color' => $d->color ?? '#cbd5e1'
            ])
            ->values()
            ->toArray();
        });
    }

    public function getRecentActivity(): array
    {
        $userId = Auth::id();
        return Cache::remember("dashboard_recent_activity_{$userId}", self::CACHE_TTL, function () {
            $learners = Auth::user()->learners()->latest()->take(3)->get()->map(fn($l) => [
                'type' => 'learner',
                'name' => $l->full_name,
                'date' => $l->created_at,
                'resource' => $l
            ]);

            $operators = Auth::user()->operators()->latest()->take(3)->get()->map(fn($o) => [
                'type' => 'operator',
                'name' => $o->name,
                'date' => $o->created_at,
                'resource' => $o
            ]);

            return $learners->concat($operators)->sortByDesc('date')->take(5)->values()->toArray();
        });
    }

    public function getOperatorWorkload(): array
    {
        $userId = Auth::id();
        return Cache::remember("dashboard_workload_{$userId}", self::CACHE_TTL, function () {
            $operators = Auth::user()->operators()
                ->with(['appointments' => function($q) {
                    $q->whereBetween('starts_at', [now()->startOfMonth(), now()->endOfMonth()]);
                }])
                ->get();

            return $operators->map(function($o) {
                $minutes = $o->appointments->sum(function($a) {
                    return $a->starts_at->diffInMinutes($a->ends_at);
                });
                return [
                    'name' => $o->name,
                    'hours' => round($minutes / 60, 1),
                    'color' => $o->color ?? '#6366f1'
                ];
            })
            ->filter(fn($o) => $o['hours'] > 0)
            ->values()
            ->toArray();
        });
    }
}

<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ReportSummaryService
{
    private const CACHE_TTL = 900; // 15 minutes

    private const CACHE_VERSION = 'v3';

    public function getOperatorSummary(int $month, int $year): Collection
    {
        $userId = Auth::id();
        $v = self::CACHE_VERSION;

        return Cache::remember("report_{$v}_{$userId}_operators_{$month}_{$year}", self::CACHE_TTL, function () use ($month, $year) {
            $appointments = $this->getPastAppointments($month, $year);

            return $appointments
                ->flatMap(function ($appointment) {
                    $minutes = max(0, $appointment->starts_at->diffInMinutes($appointment->ends_at));
                    $disciplineName = $appointment->discipline?->getTranslation('name', app()->getLocale()) ?? __('Other');

                    return $appointment->operators->flatMap(fn ($operator) => $appointment->learners->map(fn ($learner) => [
                        'operator' => $operator,
                        'learner' => $learner,
                        'discipline' => $disciplineName,
                        'minutes' => $minutes,
                    ]));
                })
                ->groupBy(fn ($item) => $item['operator']->id)
                ->map(function ($group) {
                    $operator = $group->first()['operator'];

                    $disciplines = $group->pluck('discipline')->unique()->values();

                    $breakdown = $group->groupBy(fn ($item) => $item['learner']->id)
                        ->map(function ($items) {
                            $learner = $items->first()['learner'];

                            return [
                                'resource' => $learner,
                                'name' => $learner->full_name,
                                'hours' => round($items->sum('minutes') / 60, 1),
                            ];
                        })
                        ->sortByDesc('hours')
                        ->values();

                    return [
                        'resource' => $operator,
                        'total_hours' => round($group->sum('minutes') / 60, 1),
                        'disciplines' => $disciplines,
                        'breakdown' => $breakdown,
                    ];
                })
                ->sortByDesc('total_hours')
                ->values();
        });
    }

    public function getLearnerSummary(int $month, int $year): Collection
    {
        $userId = Auth::id();
        $v = self::CACHE_VERSION;

        return Cache::remember("report_{$v}_{$userId}_learners_{$month}_{$year}", self::CACHE_TTL, function () use ($month, $year) {
            $appointments = $this->getPastAppointments($month, $year);

            return $appointments
                ->flatMap(function ($appointment) {
                    $minutes = max(0, $appointment->starts_at->diffInMinutes($appointment->ends_at));
                    $disciplineName = $appointment->discipline?->getTranslation('name', app()->getLocale()) ?? __('Other');

                    $tz = config('app.display_timezone', 'Europe/Rome');

                    return $appointment->learners->flatMap(fn ($learner) => $appointment->operators->map(fn ($operator) => [
                        'learner' => $learner,
                        'operator' => $operator,
                        'discipline' => $disciplineName,
                        'date' => $appointment->starts_at->timezone($tz),
                        'start_time' => $appointment->starts_at->timezone($tz)->format('H:i'),
                        'end_time' => $appointment->ends_at->timezone($tz)->format('H:i'),
                        'minutes' => $minutes,
                    ]));
                })
                ->groupBy(fn ($item) => $item['learner']->id)
                ->map(function ($group) {
                    $learner = $group->first()['learner'];

                    $disciplines = $group->pluck('discipline')->unique()->values();

                    $breakdown = $group->sortBy(fn ($item) => $item['date'])
                        ->map(fn ($item) => [
                            'resource' => $item['operator'],
                            'operator_name' => $item['operator']->name,
                            'date' => $item['date']->translatedFormat('D d'),
                            'time' => $item['start_time'].'–'.$item['end_time'],
                            'hours' => round($item['minutes'] / 60, 1),
                        ])
                        ->values();

                    return [
                        'resource' => $learner,
                        'total_hours' => round($group->sum('minutes') / 60, 1),
                        'disciplines' => $disciplines,
                        'breakdown' => $breakdown,
                    ];
                })
                ->sortByDesc('total_hours')
                ->values();
        });
    }

    private function getPastAppointments(int $month, int $year): Collection
    {
        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        return Auth::user()
            ->appointments()
            ->with(['operators', 'learners', 'discipline'])
            ->where('ends_at', '<', now())
            ->whereBetween('ends_at', [$startOfMonth, $endOfMonth])
            ->get();
    }
}

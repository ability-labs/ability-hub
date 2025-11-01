<?php

namespace App\Actions\Appointments;

use App\Exceptions\WeeklyPlanException;
use App\Models\User;
use App\Services\WeeklyPlannerService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PlanWeeklyAppointments
{
    private const LEARNER_SCHEDULING_ORDER = [
        'field' => 'created_at',
        'direction' => 'asc',
    ];

    public function execute(User $user, Carbon $startFrom, Collection $learners): array
    {
        $service = new WeeklyPlannerService($user);
        $appointments = [];
        $errors = [];

        foreach ($this->orderLearnersForScheduling($learners) as $learner) {
            try {
                $apps = $service->scheduleForLearner($learner, $startFrom);
                $appointments[$learner->id] = [
                    'learner'      => $learner,
                    'appointments' => $apps,
                ];
            } catch (WeeklyPlanException $e) {
                // Propaga messaggio specifico: no operator, no available slots, insufficient capacity, etc.
                $errors["learners.{$learner->id}"] = [$e->getMessage()];
            }
        }

        if (empty($appointments)) {
            throw ValidationException::withMessages($errors ?: [
                'learners' => ['Impossibile pianificare appuntamenti per i selezionati.'],
            ]);
        }

        return [
            'appointments' => $appointments, // successi (anche parziali tra learners)
            'errors'       => $errors,       // learners falliti (incl. insufficient capacity)
        ];
    }

    private function orderLearnersForScheduling(Collection $learners): Collection
    {
        $direction = strtolower(self::LEARNER_SCHEDULING_ORDER['direction']);

        $ordered = $direction === 'desc'
            ? $learners->sortByDesc(self::LEARNER_SCHEDULING_ORDER['field'])
            : $learners->sortBy(self::LEARNER_SCHEDULING_ORDER['field']);

        return $ordered->values();
    }
}

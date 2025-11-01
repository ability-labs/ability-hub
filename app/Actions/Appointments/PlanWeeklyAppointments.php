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
    /**
     * Manteniamo un ordine deterministico dei learner per evitare risultati
     * differenti tra esecuzioni consecutive: i più "anziani" (created_at ASC)
     * vengono schedulati prima così da preservare la priorità implicita.
     */
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
                // chiave “a misura” per la UI; puoi usare anche "learners.{$learner->id}"
                $errors["learners.{$learner->id}"] = [$e->getMessage()];
            }
        }

        if (empty($appointments)) {
            // tutti falliti -> 422 standard Laravel con errors
            throw ValidationException::withMessages($errors ?: [
                'learners' => ['Impossibile pianificare appuntamenti per i selezionati.'],
            ]);
        }

        // successi parziali -> 200 con payload informativo
        return [
            'appointments' => $appointments,
            'errors'       => $errors, // la UI può mostrare warning per i learner falliti
        ];
    }

    /**
     * Centralizziamo la logica di ordinamento così che qualunque chiamante
     * ottenga sempre la stessa priorità di scheduling.
     */
    private function orderLearnersForScheduling(Collection $learners): Collection
    {
        $direction = strtolower(self::LEARNER_SCHEDULING_ORDER['direction']);

        $ordered = $direction === 'desc'
            ? $learners->sortByDesc(self::LEARNER_SCHEDULING_ORDER['field'])
            : $learners->sortBy(self::LEARNER_SCHEDULING_ORDER['field']);

        return $ordered->values();
    }
}

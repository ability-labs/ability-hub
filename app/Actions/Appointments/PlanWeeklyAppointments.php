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
    public function execute(User $user, Carbon $startFrom, Collection $learners): array
    {
        $service = new WeeklyPlannerService($user);
        $appointments = [];
        $errors = [];

        foreach ($learners as $learner) {
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
}

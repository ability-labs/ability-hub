<?php

namespace App\Services;

use App\Exceptions\WeeklyPlanException;
use App\Models\Appointment;
use App\Models\Learner;
use App\Models\Operator;
use App\Models\Slot;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WeeklyPlannerService
{

    public function __construct(protected User $user)
    {
    }

    private function getApplicationTimezone(): string
    {
        return config('app.timezone', 'UTC');
    }

    private function normalizeWeekStart(Carbon $weekStartDate): Carbon
    {
        $weekStart = $weekStartDate->copy()->startOfWeek();

        $timezone = $this->getApplicationTimezone();

        return Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $weekStart->format('Y-m-d') . ' 00:00:00',
            $timezone
        );
    }

    /**
     * Schedule weekly appointments for a given learner.
     *
     * @param Learner $learner
     * @param Carbon $weekStartDate A Carbon instance representing the Monday of the week to schedule.
     */
    public function scheduleForLearner(Learner $learner, Carbon $weekStartDate): array
    {
        // 1) Copia pulita e forzo lunedì normalizzando sul fuso orario dell'applicazione
        $weekStart = $this->normalizeWeekStart($weekStartDate);

        // 2) Input validation senza mutate
        $this->validateInputs($learner, $weekStart);

        $learner->loadMissing('slots', 'operators.slots');

        $operators = $learner->operators->sortBy('name')->values();

        if ($operators->isEmpty()) {
            throw WeeklyPlanException::noOperator($learner->id);
        }

        $minutesToSchedule = $this->getRemainingMinutesForWeek($learner, $weekStart);
        if ($minutesToSchedule <= 0) {
            throw WeeklyPlanException::alreadyFulfilled($learner->id);
        }

        // Get available slots with conflict checking
        $availableSlots = $this->getAvailableSlots($learner, $operators, $weekStart);

        if ($availableSlots->isEmpty()) {
            throw WeeklyPlanException::noAvailableSlots($learner->id);
        }

        return DB::transaction(function () use ($learner, $availableSlots, $weekStart, $minutesToSchedule) {
            $appointments = $this->createAppointmentsFromSlots($learner, $availableSlots, $weekStart, $minutesToSchedule);

            if (empty($appointments)) {
                throw WeeklyPlanException::allSlotsConflict($learner->id);
            }

            return $appointments;
        });
    }

    /**
     * Validate inputs for scheduling
     */
    private function validateInputs(Learner $learner, Carbon $weekStartDate): void
    {
        if (!$learner || !$learner->exists) {
            throw new WeeklyPlanException('Invalid Learner provided', WeeklyPlanException::INVALID_LEARNER);
        }

        if (!$weekStartDate->isMonday()) {
            throw new WeeklyPlanException('Week start date is not a Monday, adjusting to start of week', WeeklyPlanException::INVALID_DATE);
        }

        if ($learner->weekly_minutes <= 0) {
            throw new WeeklyPlanException( "Learner {$learner->id} has no weekly minutes to schedule", WeeklyPlanException::NO_WEEKLY_MINUTES);
        }
    }

    /**
     * Get available slots prioritized by learner preferences with conflict checking
     */
    private function getAvailableSlots(Learner $learner, Collection $operators, Carbon $weekStartDate): Collection
    {
        $learnerSlots = $learner->slots->keyBy('id');

        $operatorSlotsByOperator = $operators->mapWithKeys(function (Operator $operator) {
            return [$operator->id => $operator->slots->keyBy('id')];
        });

        // Determine prioritized slots based on learner availability while
        // preserving the priority order between learner-declared and fallback slots.
        $ordered = $this->getPrioritizedSlots($learnerSlots, $operatorSlotsByOperator, $operators);

        // Filter out slots that would create conflicts (time overlaps)
        return $this->filterConflictingSlots($ordered, $learner, $weekStartDate);
    }


    /**
     * Get slots prioritized by learner preferences
     */
    private function getPrioritizedSlots(
        Collection $learnerSlots,
        Collection $operatorSlotsByOperator,
        Collection $operators
    ): Collection
    {
        $orderedLearnerSlots = $this->sortSlotsByWeekdayTime($learnerSlots)->values();

        if ($orderedLearnerSlots->isEmpty()) {
            return $this->buildFallbackAssignments($operators, $operatorSlotsByOperator, collect())->values();
        }

        $assignments = collect();

        foreach ($orderedLearnerSlots as $slot) {
            /** @var Operator|null $matchingOperator */
            $matchingOperator = $operators->first(function (Operator $operator) use ($operatorSlotsByOperator, $slot) {
                return $operatorSlotsByOperator->get($operator->id, collect())->has($slot->id);
            });

            if ($matchingOperator) {
                $assignments->push([
                    'slot' => $operatorSlotsByOperator->get($matchingOperator->id)->get($slot->id),
                    'operator' => $matchingOperator,
                ]);

                $operatorSlotsByOperator->get($matchingOperator->id)->forget($slot->id);
            }
        }

        $fallbackAssignments = $this->buildFallbackAssignments($operators, $operatorSlotsByOperator, $orderedLearnerSlots);

        return $assignments->concat($fallbackAssignments)->values();
    }

    private function buildFallbackAssignments(
        Collection $operators,
        Collection $operatorSlotsByOperator,
        Collection $learnerSlots
    ): Collection {
        return $operators->flatMap(function (Operator $operator) use ($operatorSlotsByOperator, $learnerSlots) {
            $slots = $operatorSlotsByOperator->get($operator->id, collect());

            if ($slots->isEmpty()) {
                return collect();
            }

            $sortedSlots = $this->sortRemainingSlotsForFallback($slots, $learnerSlots)->values();

            return $sortedSlots->map(fn (Slot $slot) => [
                'slot' => $slot,
                'operator' => $operator,
            ]);
        });
    }

    private function sortRemainingSlotsForFallback(Collection $slots, Collection $learnerSlots): Collection
    {
        if ($slots->isEmpty()) {
            return collect();
        }

        return $slots->sortBy(function (Slot $slot) use ($learnerSlots) {
            return $this->fallbackSortScore($slot, $learnerSlots);
        });
    }

    private function fallbackSortScore(Slot $slot, Collection $learnerSlots): int
    {
        $baseIndex = $this->slotMinutesIndex($slot);

        if ($learnerSlots->isEmpty()) {
            return $baseIndex;
        }

        $closestDistance = $learnerSlots
            ->map(fn (Slot $reference) => abs($baseIndex - $this->slotMinutesIndex($reference)))
            ->min();

        return ($closestDistance * 100000) + $baseIndex;
    }

    private function slotMinutesIndex(Slot $slot): int
    {
        return ($slot->week_day - 1) * 24 * 60
            + ($slot->start_time_hour * 60)
            + $slot->start_time_minute;
    }

    private function sortSlotsByWeekdayTime(Collection $slots): Collection
    {
        return $slots->sortBy(function ($slot) {
            return ($slot->week_day * 10000) + ($slot->start_time_hour * 100) + $slot->start_time_minute;
        });
    }

    /**
     * Filter out slots that would create scheduling conflicts
     */
    private function filterConflictingSlots(Collection $assignments, Learner $learner, Carbon $weekStartDate): Collection
    {
        return $assignments->filter(function (array $assignment) use ($learner, $weekStartDate) {
            /** @var Slot $slot */
            $slot = $assignment['slot'];
            /** @var Operator $operator */
            $operator = $assignment['operator'];

            $appointmentStartTime = $this->calculateAppointmentStartTime($slot, $weekStartDate);
            $appointmentEndTime = $appointmentStartTime->copy()->addMinutes($slot->duration_minutes);

            // Check for conflicts with existing appointments
            return !$this->hasConflictingAppointment($learner, $operator, $appointmentStartTime, $appointmentEndTime);
        })->values();
    }

    /**
     * Calculate the exact start time for an appointment based on slot and week
     */
    private function calculateAppointmentStartTime(Slot $slot, Carbon $weekStartDate): Carbon
    {
        return $weekStartDate->copy()
            ->startOfDay()
            ->addDays($slot->week_day - 1) // week_day 1=Monday
            ->setTime($slot->start_time_hour, $slot->start_time_minute);
    }

    /**
     * Check if there's a conflicting appointment for either learner or operator
     */
    private function hasConflictingAppointment(Learner $learner, Operator $operator, Carbon $startTime, Carbon $endTime): bool
    {
        // Check learner conflicts
        $learnerConflicts = Appointment::where('learner_id', $learner->id)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where('starts_at', '<', $endTime)
                    ->where('ends_at', '>', $startTime);
            })
            ->exists();

        // Check operator conflicts
        $operatorConflicts = Appointment::where('operator_id', $operator->id)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where('starts_at', '<', $endTime)
                    ->where('ends_at', '>', $startTime);
            })
            ->exists();

        return $learnerConflicts || $operatorConflicts;
    }

    /**
     * Create appointments from a collection of slots until the required minutes are met.
     * Enforces: max 1 appointment per learner per day (week).
     */


    private function createAppointmentsFromSlots(Learner $learner, Collection $assignments, Carbon $weekStartDate, int $minutesToSchedule): array
    {
        $appointments = [];

        // Pre-calc giorni già occupati dal learner nella settimana (1=Mon..7=Sun)
        $weekEnd = $weekStartDate->copy()->endOfWeek();
        $daysTaken = $this->getLearnerScheduledDaysInWeek($learner, $weekStartDate, $weekEnd);

        foreach ($assignments as $assignment) {
            /** @var Slot $slot */
            $slot = $assignment['slot'];
            /** @var Operator $operator */
            $operator = $assignment['operator'];

            if ($minutesToSchedule <= 0) {
                break;
            }

            $slotDay = (int) $slot->week_day; // 1 = Monday

            // Skip slot if the learner already has an appointment that day
            if (in_array($slotDay, $daysTaken, true)) {
                continue;
            }

            $appointmentStart = $this->calculateAppointmentStartTime($slot, $weekStartDate);
            $useMinutes = min($slot->duration_minutes, $minutesToSchedule);
            $appointmentEnd = $appointmentStart->copy()->addMinutes($useMinutes);

            // Check for conflicts with DB (learner/operator) - safety
            if ($this->hasConflictingAppointment($learner, $operator, $appointmentStart, $appointmentEnd)) {
                // even if slot day not taken, time conflict means skip
                continue;
            }

            // Create appointment (inside transaction in caller)
            $created = Appointment::create([
                'user_id'          => $this->user->id,
                'learner_id'       => $learner->id,
                'operator_id'      => $operator->id,
                'discipline_id'    => $slot->discipline_id,
                'title'            => $this->generateAppointmentTitle($learner, $operator),
                'starts_at'        => $appointmentStart,
                'ends_at'          => $appointmentEnd,
                'duration_minutes' => $useMinutes,
                'comments'         => '',
            ]);

            // Track created appointment and mark day as taken
            $appointments[] = $created;
            $daysTaken[] = $slotDay;

            // Decrement remaining minutes
            $minutesToSchedule -= $useMinutes;
        }

        return $appointments;
    }

    /**
     * Helper: returns array of week_day integers already scheduled for the learner in the week.
     * (1 = Monday ... 7 = Sunday)
     */
    private function getLearnerScheduledDaysInWeek(Learner $learner, Carbon $weekStart, Carbon $weekEnd): array
    {
        $existing = $learner->appointments()
            ->whereBetween('starts_at', [$weekStart, $weekEnd])
            ->get(['starts_at']);

        $days = [];
        foreach ($existing as $appt) {
            // use dayOfWeekIso to get 1..7 (Monday..Sunday)
            $days[] = (int) $appt->starts_at->copy()->setTimezone($this->getApplicationTimezone())->dayOfWeekIso;
        }

        return array_values(array_unique($days));
    }


    /**
     * Generate appointment title
     */
    private function generateAppointmentTitle(Learner $learner, Operator $operator): string
    {
        return trim($learner->name) . ' / ' . trim($operator->name);
    }

    /**
     * Calculate how many minutes are still to be scheduled for the learner in a given week.
     */
    private function getRemainingMinutesForWeek(Learner $learner, Carbon $weekStart): int
    {
        $weekEnd = $weekStart->copy()->endOfWeek();

        $already = $learner->appointments()
            ->whereBetween('starts_at', [$weekStart, $weekEnd])
            ->sum('duration_minutes');

        return max(0, $learner->weekly_minutes - $already);
    }

    /**
     * Get scheduling summary for a learner in a given week
     */
    public function getSchedulingSummary(Learner $learner, Carbon $weekStartDate): array
    {
        $weekStart = $this->normalizeWeekStart($weekStartDate);
        $weekEndDate = $weekStart->copy()->endOfWeek();

        $appointments = $learner->appointments()
            ->whereBetween('starts_at', [$weekStart, $weekEndDate])
            ->get();

        $scheduledMinutes = $appointments->sum('duration_minutes');
        $remainingMinutes = max(0, $learner->weekly_minutes - $scheduledMinutes);

        return [
            'learner_id' => $learner->id,
            'week_start' => $weekStart->format('Y-m-d'),
            'weekly_minutes_target' => $learner->weekly_minutes,
            'scheduled_minutes' => $scheduledMinutes,
            'remaining_minutes' => $remainingMinutes,
            'appointments_count' => $appointments->count(),
            'completion_percentage' => $learner->weekly_minutes > 0 ?
                round(($scheduledMinutes / $learner->weekly_minutes) * 100, 2) : 0,
        ];
    }
}

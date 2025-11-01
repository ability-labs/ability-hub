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

        $learner->loadMissing([
            'slots',
            'operators' => fn ($query) => $query->with('slots'),
        ]);

        $operators = $this->sortOperatorsByPriority($learner->operators);

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
     * Ensure operators are ordered using the pivot priority with sensible fallbacks.
     */
    private function sortOperatorsByPriority(Collection $operators): Collection
    {
        return $operators
            ->sortBy(function (Operator $operator) {
                $priority = data_get($operator, 'pivot.priority');

                return [
                    $this->normalizeOperatorPriority($priority),
                    $operator->name ?? $operator->id,
                ];
            })
            ->values();
    }

    private function normalizeOperatorPriority(mixed $priority): int
    {
        return is_numeric($priority)
            ? (int) $priority
            : Learner::DEFAULT_OPERATOR_PRIORITY;
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

        $operatorSlotsBySignature = $operators->mapWithKeys(function (Operator $operator) {
            return [$operator->id => $operator->slots->keyBy(fn (Slot $slot) => $this->slotSignature($slot))];
        });

        // Determine prioritized slots based on learner availability while
        // preserving the priority order between learner-declared and fallback slots.
        $ordered = $this->getPrioritizedSlots(
            $learnerSlots,
            $operatorSlotsByOperator,
            $operatorSlotsBySignature,
            $operators
        );

        // Filter out slots that would create conflicts (time overlaps)
        return $this->filterConflictingSlots($ordered, $learner, $weekStartDate);
    }


    /**
     * Get slots prioritized by learner preferences
     */
    private function getPrioritizedSlots(
        Collection $learnerSlots,
        Collection $operatorSlotsByOperator,
        Collection $operatorSlotsBySignature,
        Collection $operators
    ): Collection {
        $orderedLearnerSlots = $this->sortSlotsByWeekdayTime($learnerSlots)->values();

        if ($orderedLearnerSlots->isEmpty()) {
            // No learner-declared preferences: fall back entirely to the operators' calendars.
            return $this->buildFallbackAssignments($operators, $operatorSlotsByOperator, collect(), []);
        }

        $assignments = collect();
        $reservedOperatorSlots = [];

        foreach ($orderedLearnerSlots as $learnerSlot) {
            $candidateOperators = collect();

            foreach ($operators as $operator) {
                $operatorSlots = $operatorSlotsByOperator->get($operator->id, collect());
                $operatorSignatureSlots = $operatorSlotsBySignature->get($operator->id, collect());

                $slot = $operatorSlots->get($learnerSlot->id)
                    ?? $operatorSignatureSlots->get($this->slotSignature($learnerSlot));

                if (!$slot instanceof Slot) {
                    continue;
                }

                $candidateOperators->push([
                    'slot' => $slot,
                    'operator' => $operator,
                ]);

                $reservedOperatorSlots[$operator->id][$slot->id] = true;
            }

            if ($candidateOperators->isEmpty()) {
                // No operator shares this exact slot; fallback logic will handle alternative slots.
                continue;
            }

            $assignments->push([
                'candidates' => $candidateOperators,
            ]);
        }

        $fallbackAssignments = $this->buildFallbackAssignments(
            $operators,
            $operatorSlotsByOperator,
            $orderedLearnerSlots,
            $reservedOperatorSlots
        );

        return $assignments->concat($fallbackAssignments)->values();
    }

    private function buildFallbackAssignments(
        Collection $operators,
        Collection $operatorSlotsByOperator,
        Collection $learnerSlots,
        array $reservedOperatorSlots
    ): Collection {
        return $operators->flatMap(function (Operator $operator) use ($operatorSlotsByOperator, $learnerSlots, $reservedOperatorSlots) {
            $slots = $operatorSlotsByOperator->get($operator->id, collect());

            if ($slots->isEmpty()) {
                return collect();
            }

            $filteredSlots = $slots->reject(function (Slot $slot) use ($reservedOperatorSlots, $operator) {
                // Slots already considered in the preferred phase are skipped to keep them available for conflicts.
                return isset($reservedOperatorSlots[$operator->id][$slot->id]);
            });

            if ($filteredSlots->isEmpty()) {
                return collect();
            }

            $prioritizedSlots = $this->prioritizeFallbackSlots($filteredSlots, $learnerSlots);

            return $prioritizedSlots->map(fn (Slot $slot) => [
                'candidates' => collect([[
                    'slot' => $slot,
                    'operator' => $operator,
                ]]),
            ]);
        });
    }

    private function prioritizeFallbackSlots(Collection $slots, Collection $learnerSlots): Collection
    {
        if ($slots->isEmpty()) {
            return collect();
        }

        if ($learnerSlots->isEmpty()) {
            return $this->sortRemainingSlotsForFallback($slots, $learnerSlots)->values();
        }

        $orderedSlots = collect();
        $remainingSlots = $slots;

        $sameDaySameSpanSlots = $remainingSlots->filter(fn (Slot $slot) => $this->matchesLearnerDayAndSpan($slot, $learnerSlots));

        if ($sameDaySameSpanSlots->isNotEmpty()) {
            // First preference: keep alternatives aligned to the learner's day and declared span.
            $orderedSlots = $orderedSlots->concat($this->sortRemainingSlotsForFallback($sameDaySameSpanSlots, $learnerSlots));
            $remainingSlots = $remainingSlots->diff($sameDaySameSpanSlots);
        }

        $sameDaySlots = collect();

        if ($sameDaySameSpanSlots->isEmpty()) {
            $sameDaySlots = $remainingSlots->filter(fn (Slot $slot) => $this->matchesLearnerWeekDay($slot, $learnerSlots));

            if ($sameDaySlots->isNotEmpty()) {
                // No span-compatible option exists, widen the search to the same weekday regardless of span.
                $orderedSlots = $orderedSlots->concat($this->sortRemainingSlotsForFallback($sameDaySlots, $learnerSlots));
                $remainingSlots = $remainingSlots->diff($sameDaySlots);
            } else {
                // No slot on the learner's weekdays: fall back to the generic ordering to avoid leaving availability unused.
                return $orderedSlots->concat($this->sortRemainingSlotsForFallback($remainingSlots, $learnerSlots))->values();
            }
        }

        if ($remainingSlots->isNotEmpty()) {
            // After prioritised matches, keep the residual availability as the ultimate fallback layer.
            $orderedSlots = $orderedSlots->concat($this->sortRemainingSlotsForFallback($remainingSlots, $learnerSlots));
        }

        return $orderedSlots->values();
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

    private function matchesLearnerDayAndSpan(Slot $slot, Collection $learnerSlots): bool
    {
        return $learnerSlots->contains(function (Slot $preferredSlot) use ($slot) {
            return $slot->week_day === $preferredSlot->week_day
                && $this->slotSpanValue($slot) === $this->slotSpanValue($preferredSlot);
        });
    }

    private function matchesLearnerWeekDay(Slot $slot, Collection $learnerSlots): bool
    {
        return $learnerSlots->contains(fn (Slot $preferredSlot) => $slot->week_day === $preferredSlot->week_day);
    }

    private function slotSpanValue(Slot $slot): ?string
    {
        return $slot->getAttribute('day_span');
    }

    private function slotSignature(Slot $slot): string
    {
        return implode(':', [
            $slot->week_day,
            $slot->start_time_hour,
            $slot->start_time_minute,
            $slot->duration_minutes,
            $slot->discipline_id,
        ]);
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
        $reservedSlots = [];

        return $assignments->reduce(function (Collection $carry, array $assignment) use ($learner, $weekStartDate, &$reservedSlots) {
            $candidates = $assignment['candidates'] ?? collect([$assignment]);

            if ($candidates instanceof Collection) {
                $candidates = $candidates->values();
            } else {
                $candidates = collect($candidates);
            }

            foreach ($candidates as $candidate) {
                /** @var Slot $slot */
                $slot = $candidate['slot'];
                /** @var Operator $operator */
                $operator = $candidate['operator'];

                $reservationKey = $operator->id . ':' . $slot->id;

                if (isset($reservedSlots[$reservationKey])) {
                    // Another learner already booked this operator slot, try the next candidate.
                    continue;
                }

                $appointmentStartTime = $this->calculateAppointmentStartTime($slot, $weekStartDate);
                $appointmentEndTime = $appointmentStartTime->copy()->addMinutes($slot->duration_minutes);

                if ($this->hasConflictingAppointment($learner, $operator, $appointmentStartTime, $appointmentEndTime)) {
                    // Operator is busy in this time range, fallback to the next candidate.
                    continue;
                }

                $reservedSlots[$reservationKey] = true;

                $carry->push([
                    'slot' => $slot,
                    'operator' => $operator,
                ]);

                // As soon as a conflict-free operator is found we stop iterating candidates for this slot.
                return $carry;
            }

            // No available operator found for this slot, nothing is added to the list.
            return $carry;
        }, collect())->values();
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

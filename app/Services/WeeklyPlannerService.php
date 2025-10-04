<?php

namespace App\Services;

use App\Exceptions\WeeklyPlanException;
use App\Models\Appointment;
use App\Models\Learner;
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

    /**
     * Schedule weekly appointments for a given learner.
     *
     * @param Learner $learner
     * @param Carbon $weekStartDate A Carbon instance representing the Monday of the week to schedule.
     */
    public function scheduleForLearner(Learner $learner, Carbon $weekStartDate): array
    {
        // 1) Copia pulita e forzo lunedì
        $weekStart = $weekStartDate->copy()->startOfWeek();

        // 2) Input validation senza mutate
        $this->validateInputs($learner, $weekStart);

        $operator = $learner->operator;
        if (!$operator) {
            throw WeeklyPlanException::noOperator($learner->id);
        }

        $minutesToSchedule = $this->getRemainingMinutesForWeek($learner, $weekStart);
        if ($minutesToSchedule <= 0) {
            throw WeeklyPlanException::alreadyFulfilled($learner->id);
        }

        // Get available slots with conflict checking
        $availableSlots = $this->getAvailableSlots($learner, $operator, $weekStart);

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
    private function getAvailableSlots(Learner $learner, $operator, Carbon $weekStartDate): Collection
    {
        $learnerSlots = $learner->slots()->get()->keyBy('id');
        $operatorSlots = $operator->slots()->get()->keyBy('id');

        // Determine prioritized slots based on learner availability while
        // preserving the priority order between learner-declared and fallback slots.
        $ordered = $this->getPrioritizedSlots($learnerSlots, $operatorSlots);

        // Filter out slots that would create conflicts (time overlaps)
        return $this->filterConflictingSlots($ordered, $learner, $operator, $weekStartDate);
    }


    /**
     * Get slots prioritized by learner preferences
     */
    private function getPrioritizedSlots(Collection $learnerSlots, Collection $operatorSlots): Collection
    {
        if ($learnerSlots->isEmpty()) {
            // Case: Learner has no declared availability - use operator's slots
            return $this->sortSlotsByWeekdayTime($operatorSlots)->values();
        }

        // Learner slots must always come first. Sort them chronologically while
        // maintaining their priority over the operator-only availability.
        $prioritized = $this->sortSlotsByWeekdayTime($learnerSlots)->values();

        // After exhausting learner preferences we can fallback to operator-only slots.
        $operatorFallback = $this->sortSlotsByWeekdayTime(
            $operatorSlots->diffKeys($learnerSlots)
        )->values();

        return $prioritized->concat($operatorFallback);
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
    private function filterConflictingSlots(Collection $slots, Learner $learner, $operator, Carbon $weekStartDate): Collection
    {
        return $slots->filter(function ($slot) use ($learner, $operator, $weekStartDate) {
            $appointmentStartTime = $this->calculateAppointmentStartTime($slot, $weekStartDate);
            $appointmentEndTime = $appointmentStartTime->copy()->addMinutes($slot->duration_minutes);

            // Check for conflicts with existing appointments
            return !$this->hasConflictingAppointment($learner, $operator, $appointmentStartTime, $appointmentEndTime);
        });
    }

    /**
     * Calculate the exact start time for an appointment based on slot and week
     */
    private function calculateAppointmentStartTime(Slot $slot, Carbon $weekStartDate): Carbon
    {
        return $weekStartDate->copy()
            ->addDays($slot->week_day - 1) // week_day 1=Monday
            ->setTime($slot->start_time_hour, $slot->start_time_minute);
    }

    /**
     * Check if there's a conflicting appointment for either learner or operator
     */
    private function hasConflictingAppointment(Learner $learner, $operator, Carbon $startTime, Carbon $endTime): bool
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
    private function createAppointmentsFromSlots(Learner $learner, Collection $slots, Carbon $weekStartDate, int $minutesToSchedule): array
    {
        $appointments = [];

        // Pre-calc giorni già occupati dal learner nella settimana (1=Mon..7=Sun)
        $weekEnd = $weekStartDate->copy()->endOfWeek();
        $daysTaken = $this->getLearnerScheduledDaysInWeek($learner, $weekStartDate, $weekEnd);

        foreach ($slots as $slot) {
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
            if ($this->hasConflictingAppointment($learner, $learner->operator, $appointmentStart, $appointmentEnd)) {
                // even if slot day not taken, time conflict means skip
                continue;
            }

            // Create appointment (inside transaction in caller)
            $created = Appointment::create([
                'user_id'          => $this->user->id,
                'learner_id'       => $learner->id,
                'operator_id'      => $learner->operator_id,
                'discipline_id'    => $slot->discipline_id,
                'title'            => trim($learner->first_name . ' ' . $learner->last_name)
                    . ' / ' .
                    trim($learner->operator->first_name . ' ' . $learner->operator->last_name),
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
            $days[] = (int) $appt->starts_at->dayOfWeekIso;
        }

        return array_values(array_unique($days));
    }


    /**
     * Generate appointment title
     */
    private function generateAppointmentTitle(Learner $learner): string
    {
        return trim($learner->name) . ' / ' . trim($learner->operator->name);
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
        $weekEndDate = $weekStartDate->copy()->endOfWeek();

        $appointments = $learner->appointments()
            ->whereBetween('starts_at', [$weekStartDate, $weekEndDate])
            ->get();

        $scheduledMinutes = $appointments->sum('duration_minutes');
        $remainingMinutes = max(0, $learner->weekly_minutes - $scheduledMinutes);

        return [
            'learner_id' => $learner->id,
            'week_start' => $weekStartDate->format('Y-m-d'),
            'weekly_minutes_target' => $learner->weekly_minutes,
            'scheduled_minutes' => $scheduledMinutes,
            'remaining_minutes' => $remainingMinutes,
            'appointments_count' => $appointments->count(),
            'completion_percentage' => $learner->weekly_minutes > 0 ?
                round(($scheduledMinutes / $learner->weekly_minutes) * 100, 2) : 0,
        ];
    }
}

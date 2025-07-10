<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Learner;
use App\Models\Slot;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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
    public function scheduleForLearner(Learner $learner, Carbon $weekStartDate): void
    {
        // 1) Copia pulita e forzo lunedì
        $weekStart = $weekStartDate->copy()->startOfWeek();

        // 2) Input validation senza mutate
        if (!$this->validateInputs($learner, $weekStart)) {
            return;
        }

        $operator = $learner->operator;
        if (!$operator) {
            Log::warning("No operator assigned to learner {$learner->id}");
            return;
        }

        $minutesToSchedule = $this->getRemainingMinutesForWeek($learner, $weekStart);
        if ($minutesToSchedule <= 0) {
            Log::info("Weekly minutes already fulfilled for learner {$learner->id}");
            return;
        }

        // Get available slots with conflict checking
        $availableSlots = $this->getAvailableSlots($learner, $operator, $weekStart);

        if ($availableSlots->isEmpty()) {
            Log::warning("No available slots found for learner {$learner->id} and operator {$operator->id}");
            return;
        }

        $this->createAppointmentsFromSlots($learner, $availableSlots, $weekStart, $minutesToSchedule);
    }

    /**
     * Validate inputs for scheduling
     */
    private function validateInputs(Learner $learner, Carbon $weekStartDate): bool
    {
        if (!$learner || !$learner->exists) {
            Log::error("Invalid learner provided");
            return false;
        }

        if (!$weekStartDate->isMonday()) {
            Log::warning("Week start date is not a Monday, adjusting to start of week");
            //$weekStartDate->startOfWeek();
        }

        if ($learner->weekly_minutes <= 0) {
            Log::info("Learner {$learner->id} has no weekly minutes to schedule");
            return false;
        }

        return true;
    }

    /**
     * Get available slots prioritized by learner preferences with conflict checking
     */
    private function getAvailableSlots(Learner $learner, $operator, Carbon $weekStartDate): Collection
    {
        $learnerSlots = $learner->slots()->get()->keyBy('id');
        $operatorSlots = $operator->slots()->get()->keyBy('id');

        // Determine prioritized slots based on learner availability
        $prioritizedSlots = $this->getPrioritizedSlots($learnerSlots, $operatorSlots);

        // Filter out slots that would create conflicts
        return $this->filterConflictingSlots($prioritizedSlots, $learner, $operator, $weekStartDate);
    }

    /**
     * Get slots prioritized by learner preferences
     */
    private function getPrioritizedSlots(Collection $learnerSlots, Collection $operatorSlots): Collection
    {
        if ($learnerSlots->isEmpty()) {
            // Case: Learner has no declared availability - use operator's slots
            return $operatorSlots;
        }

        // Case: Learner has declared availability
        $commonSlots = $operatorSlots->intersectByKeys($learnerSlots);
        $operatorOnlySlots = $operatorSlots->diffKeys($learnerSlots);

        // Priority: common slots first, then operator's fallback slots
        return $commonSlots->concat($operatorOnlySlots);
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
     */
    private function createAppointmentsFromSlots(Learner $learner, Collection $slots, Carbon $weekStartDate, int $minutesToSchedule): void
    {
        foreach ($slots as $slot) {
            if ($minutesToSchedule <= 0) {
                break;
            }

            $appointmentStart = $this->calculateAppointmentStartTime($slot, $weekStartDate);
            // usa solo i minuti residui
            $useMinutes = min($slot->duration_minutes, $minutesToSchedule);
            $appointmentEnd = $appointmentStart->copy()->addMinutes($useMinutes);

            // doppio controllo conflitti...
            if ($this->hasConflictingAppointment($learner, $learner->operator, $appointmentStart, $appointmentEnd)) {
                continue;
            }

            Appointment::create([
                'user_id'          => $this->user->id,
                'learner_id'       => $learner->id,
                'operator_id'      => $learner->operator_id,
                'discipline_id'    => $slot->discipline_id,
                'title'            => trim($learner->first_name . ' ' . $learner->last_name)
                    . ' / '
                    . trim($learner->operator->first_name . ' ' . $learner->operator->last_name),
                'starts_at'        => $appointmentStart,
                'ends_at'          => $appointmentEnd,
                'duration_minutes' => $useMinutes,
                'comments'         => '',    // obbligatorio
            ]);

            $minutesToSchedule -= $useMinutes;
        }
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

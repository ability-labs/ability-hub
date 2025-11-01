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

class WeeklyPlannerService
{
    public function __construct(protected User $user)
    {
    }

    /**
     * Main entry point:
     * - Ensures weekly_minutes are fully covered using multi-level fallback.
     * - Prioritizes learner-declared slots, operator priority, and same-day alignment.
     */
    public function scheduleForLearner(Learner $learner, Carbon $weekStartDate): array
    {
        $weekStart = $this->normalizeWeekStart($weekStartDate);
        $this->validateInputs($learner, $weekStart);

        $learner->loadMissing([
            'slots',
            'operators' => fn($q) => $q->with('slots'),
        ]);

        $operators = $this->sortOperatorsByPriority($learner->operators);
        if ($operators->isEmpty()) {
            throw WeeklyPlanException::noOperator($learner->id);
        }

        $minutesToSchedule = $this->getRemainingMinutesForWeek($learner, $weekStart);
        if ($minutesToSchedule <= 0) {
            throw WeeklyPlanException::alreadyFulfilled($learner->id);
        }

        return DB::transaction(function () use ($learner, $operators, $weekStart, $minutesToSchedule) {
            $appointments = $this->planFullWeek($learner, $operators, $weekStart, $minutesToSchedule);
            if (empty($appointments)) {
                throw WeeklyPlanException::noAvailableCapacity($learner->id);
            }
            return $appointments;
        });
    }

    /**
     * Core planning logic with progressive fallback layers.
     */
    private function planFullWeek(Learner $learner, Collection $operators, Carbon $weekStart, int $minutesTarget): array
    {
        $appointments = [];
        $remaining = $minutesTarget;
        $reserved = [];
        $weekEnd = $weekStart->copy()->endOfWeek();
        $daysTaken = $this->getLearnerScheduledDaysInWeek($learner, $weekStart, $weekEnd);
        $learnerSlots = $this->sortSlotsByWeekdayTime($learner->slots);

        // MAIN LOOP
        foreach ($learnerSlots as $learnerSlot) {
            if ($remaining <= 0) break;

            $assigned = $this->tryAssignSlot($learner, $learnerSlot, $operators, $weekStart, $daysTaken, $reserved, $remaining);
            if ($assigned) {
                [$app, $usedMinutes, $day] = $assigned;
                $appointments[] = $app;
                $remaining -= $usedMinutes;
                $daysTaken[] = $day;
            }
        }

        // If still not fullfilled → extended fallback
        if ($remaining > 0) {
            $extended = $this->extendedFallback($learner, $operators, $weekStart, $remaining, $daysTaken, $reserved);
            foreach ($extended['appointments'] as $a) {
                $appointments[] = $a;
            }
            $remaining = $extended['remaining'];
        }

        // If still remaining but no operator slot available at all → final fail
        if ($remaining > 0 && empty($appointments)) {
            throw WeeklyPlanException::noAvailableCapacity($learner->id);
        }

        return $appointments;
    }

    /**
     * Attempt scheduling for one learner slot, testing all operators in priority order.
     */
    private function tryAssignSlot(
        Learner $learner,
        Slot $learnerSlot,
        Collection $operators,
        Carbon $weekStart,
        array &$daysTaken,
        array &$reserved,
        int &$remaining
    ): ?array {
        foreach ($operators as $operator) {
            $match = $this->findOperatorSlotMatch($operator->slots, $learnerSlot);
            if (!$match) continue;

            if ($this->isReservedOrConflicting($learner, $operator, $match, $weekStart, $daysTaken, $reserved)) {
                continue;
            }

            return $this->createAppointment($learner, $operator, $match, $weekStart, $remaining, $daysTaken, $reserved);
        }

        // Fallback 1: same day + same span
        foreach ($operators as $operator) {
            $slots = $this->findSlotsByDayAndSpan($operator->slots, $learnerSlot->week_day, $learnerSlot->day_span);
            foreach ($slots as $slot) {
                if ($this->isReservedOrConflicting($learner, $operator, $slot, $weekStart, $daysTaken, $reserved)) continue;
                return $this->createAppointment($learner, $operator, $slot, $weekStart, $remaining, $daysTaken, $reserved);
            }
        }

        // Fallback 2: same day, any span
        foreach ($operators as $operator) {
            $slots = $operator->slots->where('week_day', $learnerSlot->week_day);
            foreach ($slots as $slot) {
                if ($this->isReservedOrConflicting($learner, $operator, $slot, $weekStart, $daysTaken, $reserved)) continue;
                return $this->createAppointment($learner, $operator, $slot, $weekStart, $remaining, $daysTaken, $reserved);
            }
        }

        return null;
    }

    /**
     * Extended fallback: search any free slot from assigned operators in the week.
     */
    private function extendedFallback(Learner $learner, Collection $operators, Carbon $weekStart, int $remaining, array &$daysTaken, array &$reserved): array
    {
        $appointments = [];

        foreach ($operators as $operator) {
            foreach ($operator->slots as $slot) {
                if ($remaining <= 0) break;
                if ($this->isReservedOrConflicting($learner, $operator, $slot, $weekStart, $daysTaken, $reserved)) continue;

                $appt = $this->createAppointment($learner, $operator, $slot, $weekStart, $remaining, $daysTaken, $reserved);
                if ($appt) {
                    [$app, $used, $day] = $appt;
                    $appointments[] = $app;
                    $remaining -= $used;
                    $daysTaken[] = $day;
                }
            }
        }

        return ['appointments' => $appointments, 'remaining' => $remaining];
    }

    /* -----------------------------------------------
       HELPERS
    ------------------------------------------------*/

    private function findSlotsByDayAndSpan(Collection $slots, int $day, string $span): Collection
    {
        return $slots->filter(fn($s) => $s->week_day === $day && $s->day_span === $span);
    }

    private function isReservedOrConflicting(Learner $learner, Operator $op, Slot $slot, Carbon $weekStart, array $daysTaken, array $reserved): bool
    {
        $key = $op->id . ':' . $slot->id;
        if (isset($reserved[$key])) return true;
        if (in_array($slot->week_day, $daysTaken, true)) return true;

        $start = $this->calculateAppointmentStartTime($slot, $weekStart);
        $end   = $start->copy()->addMinutes($slot->duration_minutes);
        return $this->hasConflictingAppointment($learner, $op, $start, $end);
    }

    private function createAppointment(Learner $learner, Operator $op, Slot $slot, Carbon $weekStart, int &$remaining, array &$daysTaken, array &$reserved): array
    {
        $key = $op->id . ':' . $slot->id;
        $reserved[$key] = true;

        $start = $this->calculateAppointmentStartTime($slot, $weekStart);
        $useMinutes = min($slot->duration_minutes, $remaining);
        $end = $start->copy()->addMinutes($useMinutes);

        $app = Appointment::create([
            'user_id'          => $this->user->id,
            'learner_id'       => $learner->id,
            'operator_id'      => $op->id,
            'discipline_id'    => $slot->discipline_id,
            'title'            => $this->generateAppointmentTitle($learner, $op),
            'starts_at'        => $start,
            'ends_at'          => $end,
            'duration_minutes' => $useMinutes,
            'comments'         => '',
        ]);

        return [$app, $useMinutes, $slot->week_day];
    }

    private function findOperatorSlotMatch(Collection $slots, Slot $learnerSlot): ?Slot
    {
        return $slots->first(fn($s) => $s->id === $learnerSlot->id || $this->slotSignature($s) === $this->slotSignature($learnerSlot));
    }

    /* -------------- Utility -------------- */

    private function normalizeWeekStart(Carbon $weekStart): Carbon
    {
        return $weekStart->copy()->startOfWeek()->setTime(0, 0);
    }

    private function validateInputs(Learner $learner, Carbon $weekStart): void
    {
        if (!$learner->exists) {
            throw WeeklyPlanException::invalidLearner($learner->id);
        }
        if (!$weekStart->isMonday()) {
            throw WeeklyPlanException::invalidDate($weekStart);
        }
        if ($learner->weekly_minutes <= 0) {
            throw WeeklyPlanException::noWeeklyMinutes($learner->id);
        }
    }

    private function slotSignature(Slot $slot): string
    {
        return "{$slot->week_day}:{$slot->start_time_hour}:{$slot->start_time_minute}:{$slot->duration_minutes}:{$slot->discipline_id}";
    }

    private function calculateAppointmentStartTime(Slot $slot, Carbon $weekStart): Carbon
    {
        return $weekStart->copy()->addDays($slot->week_day - 1)->setTime($slot->start_time_hour, $slot->start_time_minute);
    }

    private function hasConflictingAppointment(Learner $learner, Operator $op, Carbon $start, Carbon $end): bool
    {
        $learnerBusy = Appointment::where('learner_id', $learner->id)
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', $start)
            ->exists();

        $opBusy = Appointment::where('operator_id', $op->id)
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', $start)
            ->exists();

        return $learnerBusy || $opBusy;
    }

    private function getLearnerScheduledDaysInWeek(Learner $learner, Carbon $start, Carbon $end): array
    {
        return $learner->appointments()
            ->whereBetween('starts_at', [$start, $end])
            ->get()
            ->map(fn($a) => (int) $a->starts_at->dayOfWeekIso)
            ->unique()
            ->values()
            ->toArray();
    }

    private function sortOperatorsByPriority(Collection $ops): Collection
    {
        return $ops->sortBy(fn($op) => data_get($op, 'pivot.priority', 999))->values();
    }

    private function sortSlotsByWeekdayTime(Collection $slots): Collection
    {
        return $slots->sortBy(fn($s) => $s->week_day * 10000 + $s->start_time_hour * 100 + $s->start_time_minute)->values();
    }

    private function generateAppointmentTitle(Learner $learner, Operator $op): string
    {
        return trim($learner->name) . ' / ' . trim($op->name);
    }

    private function getRemainingMinutesForWeek(Learner $learner, Carbon $weekStart): int
    {
        $weekEnd = $weekStart->copy()->endOfWeek();
        $scheduled = $learner->appointments()->whereBetween('starts_at', [$weekStart, $weekEnd])->sum('duration_minutes');
        return max(0, $learner->weekly_minutes - $scheduled);
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

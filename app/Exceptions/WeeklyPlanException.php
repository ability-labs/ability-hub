<?php

namespace App\Exceptions;

use Illuminate\Support\Carbon;
use RuntimeException;

class WeeklyPlanException extends RuntimeException
{
    public const INVALID_LEARNER = 0;
    const NO_OPERATOR = 1;
    const NO_WEEKLY_MINUTES = 2;
    const ALREADY_FULFILLED = 3;
    const NO_AVAILABLE_SLOTS = 4;
    const ALL_SLOTS_CONFLICT = 5;
    public const INVALID_DATE = 6;
    public const INSUFFICIENT_CAPACITY = 7;

    public function __construct(
        string $message = '',
        int $code = 0,
    ) {
        parent::__construct($message, $code);
    }

    public static function noOperator(string $learnerId): self
    {
        return new self("Learner {$learnerId}: nessun operatore assegnato.", self::NO_OPERATOR);
    }

    public static function noWeeklyMinutes(string $learnerId): self
    {
        return new self( "Learner {$learnerId}: minuti settimanali a 0.", self::NO_WEEKLY_MINUTES);
    }

    public static function alreadyFulfilled(string $learnerId): self
    {
        return new self("Learner {$learnerId}: minuti già soddisfatti in questa settimana.", self::ALREADY_FULFILLED);
    }

    public static function noAvailableSlots(string $learnerId): self
    {
        return new self("Learner {$learnerId}: nessuna disponibilità utile.", self::NO_AVAILABLE_SLOTS);
    }

    public static function allSlotsConflict(string $learnerId): self
    {
        return new self("Learner {$learnerId}: tutti gli slot andavano in conflitto.", self::ALL_SLOTS_CONFLICT);
    }

    public static function insufficientCapacity(string $learnerId, int $remaining): self
    {
        return new self(
            "Impossibile soddisfare i weekly_minutes per il learner {$learnerId}. Minuti mancanti: {$remaining}.",
            self::INSUFFICIENT_CAPACITY
        );
    }

    public static function noAvailableCapacity(mixed $learnerId)
    {
        return new self(
            "Impossibile trovare uno slot disponibile per il learner {$learnerId}.",
            self::INSUFFICIENT_CAPACITY
        );
    }

    public static function invalidLearner(mixed $id)
    {
        return new self(
            "Learner non valido: {$id}.",
            self::INVALID_LEARNER
        );
    }

    public static function invalidDate(Carbon $weekStart)
    {
        return new self(
            "Data non valida: {$weekStart}.",
            self::INVALID_DATE
        );
    }
}

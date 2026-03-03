<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\AppointmentFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Appointment extends Model
{
    /** @use HasFactory<AppointmentFactory> */
    use HasFactory, HasUuids;

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::saved(function ($appointment) {
            if ($appointment->learner_id && !$appointment->learners()->where('learners.id', $appointment->learner_id)->exists()) {
                $appointment->learners()->syncWithoutDetaching([$appointment->learner_id]);
            }
            if ($appointment->operator_id && !$appointment->operators()->where('operators.id', $appointment->operator_id)->exists()) {
                $appointment->operators()->syncWithoutDetaching([$appointment->operator_id]);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function learner(): BelongsTo
    {
        return $this->belongsTo(Learner::class);
    }

    public function learners(): BelongsToMany
    {
        return $this->belongsToMany(Learner::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }

    public function operators(): BelongsToMany
    {
        return $this->belongsToMany(Operator::class);
    }

    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    public function getDurationAttribute(): string
    {
        return $this->starts_at
            ->diffForHumans(
                $this->ends_at,
                CarbonInterface::DIFF_ABSOLUTE
            );
    }

    public function appointmentType(): BelongsTo
    {
        return $this->belongsTo(AppointmentType::class);
    }

    public function toFullCalendar(): array
    {
        $learnerNames = $this->learners->pluck('full_name')->join(', ');
        $operatorNames = $this->operators->pluck('name')->join(', ');

        return [
            'id'      => $this->id,
            'title'   => $learnerNames . ' (' . $operatorNames . ')',
            'start'   => $this->starts_at,
            'end'     => $this->ends_at,
            'color'   => $this->operators->first()?->color ?? '#2563eb',
            'extendedProps' => [
                'learner' => $this->learners->first(), // For BC with some UI parts
                'operator'=> $this->operators->first(), // For BC with some UI parts
                'learners' => $this->learners,
                'operators' => $this->operators,
                'discipline' => $this->discipline,
                'appointment_type_id' => $this->appointment_type_id,
                'comments' => $this->comments
            ]
        ];
    }
}

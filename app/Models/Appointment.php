<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\AppointmentFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    /** @use HasFactory<AppointmentFactory> */
    use HasFactory, HasUuids;

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function learner(): BelongsTo
    {
        return $this->belongsTo(Learner::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
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

    public function toFullCalendar(): array
    {
        return [
            'id'      => $this->id,
            'title'   => $this->learner->full_name . ' (' . $this->operator->name . ')',
            'start'   => $this->starts_at,
            'end'     => $this->ends_at,
            'color'   => $this->discipline->color,
            'extendedProps' => [
                'learner' => $this->learner,
                'operator'=> $this->operator,
                'discipline' => $this->discipline,
                'comments' => $this->comments
            ]
        ];
    }
}

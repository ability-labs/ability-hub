<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Slot extends Model
{
    /** @use HasFactory<\Database\Factories\SlotFactory> */
    use HasFactory, HasUuids;

    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    public function learners(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\Learner::class,
            'availability_learner',
            'slot_id',
            'learner_id'
        );
    }

    public function operators(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\Operator::class,
            'availability_operator',
            'slot_id',
            'operator_id'
        );
    }
}

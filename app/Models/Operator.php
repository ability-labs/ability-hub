<?php

namespace App\Models;

use Database\Factories\OperatorFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Operator extends Model
{
    /** @use HasFactory<OperatorFactory> */
    use HasFactory, HasUuids;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function disciplines(): BelongsToMany
    {
        return $this->belongsToMany(Discipline::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}

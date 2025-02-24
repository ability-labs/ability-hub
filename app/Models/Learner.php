<?php

namespace App\Models;

use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Learner extends Model
{
    /** @use HasFactory<\Database\Factories\LearnerFactory> */
    use HasFactory, HasUuids;

    protected $appends = [
        'full_name',
        'age',
    ];

    protected $casts = [
        'birth_date' => 'datetime'
    ];

    public function getAgeAttribute(): string
    {
        /** @var CarbonInterval $diff */
        $diff = $this->birth_date
            ->diff(now());

        return ($diff->years >= 3) ?
            $diff->forHumans(['parts' => 1])
            : $diff->forHumans(['parts' => 2]);
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

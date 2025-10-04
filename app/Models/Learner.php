<?php

namespace App\Models;

use App\Enums\PersonGender;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'birth_date' => 'datetime',
        'gender' => PersonGender::class,
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

    public function getNameAttribute(): string
    {
        return $this->full_name;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function datasheets(): HasMany
    {
        return $this->hasMany(Datasheet::class);
    }

    public function preferences(): HasMany
    {
        return $this->hasMany(PreferenceAssessment::class);
    }

    public function slots()
    {
        return $this->belongsToMany(Slot::class, 'availability_learner');
    }

    public function operators(): BelongsToMany
    {
        return $this->belongsToMany(Operator::class, 'learner_operator')->withTimestamps();
    }

    public function getOperatorAttribute(): ?Operator
    {
        if ($this->relationLoaded('operators')) {
            return $this->getRelation('operators')->first();
        }

        return $this->operators()->first();
    }

    public function getOperatorIdAttribute(): ?string
    {
        return $this->operator?->id;
    }
}

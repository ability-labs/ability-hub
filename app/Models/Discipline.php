<?php

namespace App\Models;

use Database\Factories\DisciplineFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Spatie\Translatable\HasTranslations;

class Discipline extends Model
{
    /** @use HasFactory<DisciplineFactory> */
    use HasFactory, HasUuids, HasTranslations;

    public $translatable = [
        'name'
    ];

    public function operators(): BelongsToMany
    {
        return $this->belongsToMany(Operator::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function slots(): HasMany
    {
        return $this->hasMany(Slot::class);
    }

    public function days()
    {
        return $this->slots->pluck('week_day')->unique();
    }

    public function spans()
    {
        return $this->slots->pluck('day_span')->unique();
    }
}

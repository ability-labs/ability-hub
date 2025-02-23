<?php

namespace App\Models;

use Database\Factories\DisciplineFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
}

<?php

namespace App\Models;

use Database\Factories\PreferenceAssessmentFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreferenceAssessment extends Model
{
    /** @use HasFactory<PreferenceAssessmentFactory> */
    use HasFactory, HasUuids;

    protected $with = [
        'learner',
        'datasheet',
        'operator',
        'reinforcer'
    ];

    public function learner(): BelongsTo
    {
        return $this->belongsTo(Learner::class);
    }
    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }
    public function datasheet(): BelongsTo
    {
        return $this->belongsTo(Datasheet::class);
    }
    public function reinforcer(): BelongsTo
    {
        return $this->belongsTo(Reinforcer::class);
    }
}

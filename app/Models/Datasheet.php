<?php

namespace App\Models;

use Database\Factories\DatasheetFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\SchemalessAttributes\Casts\SchemalessAttributes;

class Datasheet extends Model
{
    /** @use HasFactory<DatasheetFactory> */
    use HasFactory,HasUuids;

    public $casts = [
        'finalized_at' => 'datetime',
        'data' => SchemalessAttributes::class,
    ];

    protected $with = [
        'type'
    ];


    public function scopeWithData(): Builder
    {
        return $this->data->modelScope();
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }

    public function learner(): BelongsTo
    {
        return $this->belongsTo(Learner::class);
    }

    public function type() :BelongsTo
    {
        return $this->belongsTo(DatasheetType::class,'type_id','id');
    }
}

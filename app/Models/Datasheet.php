<?php

namespace App\Models;

use App\Services\Datasheets\DatasheetReportFactory;
use App\Services\Datasheets\ReportAbstract;
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

    protected $appends = [
        'results'
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

    public function report(): ReportAbstract
    {
        return DatasheetReportFactory::fromDatasheet($this);
    }

    public function getDataTemplate(): array
    {
        return $this->report()->getDatasetTemplate();
    }

    public function initData(): void
    {
        $this->update(['data' => $this->getDataTemplate()]);
    }

    public function getResultsAttribute(): array
    {
        return $this->report()->report();
    }
}

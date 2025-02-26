<?php

namespace App\Models;

use Database\Factories\ReinforcerFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Reinforcer extends Model
{
    /** @use HasFactory<ReinforcerFactory> */
    use HasFactory, HasUuids, HasTranslations;

    public $translatable = [
        'name',
        'category',
        'subcategory',
    ];

    public function forSuggestions(): array
    {
        return [
            'id' => $this->id,
            'key' => $this->id,
            'name' => $this->getTranslation('name', config('app.locale')),
            'category' => $this->getTranslation('category', config('app.locale')),
            'subcategory' => $this->getTranslation('subcategory', config('app.locale')),
        ];
    }
}

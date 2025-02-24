<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class ContentCard extends Model
{
    use HasUuids, HasTranslations;

    public $translatable = [
        'title',
        'content'
    ];
}

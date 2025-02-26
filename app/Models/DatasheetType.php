<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class DatasheetType extends Model
{
    use HasTranslations;

    public $incrementing = false;

    protected $keyType = 'string';
    public $translatable = [
        'name'
    ];
}

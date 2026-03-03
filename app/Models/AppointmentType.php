<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class AppointmentType extends Model
{
    /** @use HasFactory<\Database\Factories\AppointmentTypeFactory> */
    use HasFactory, HasUuids, HasTranslations;

    protected $fillable = ['name', 'color'];

    public $translatable = ['name'];
}

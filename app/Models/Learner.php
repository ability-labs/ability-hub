<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Learner extends Model
{
    /** @use HasFactory<\Database\Factories\LearnerFactory> */
    use HasFactory, HasUuid;
}

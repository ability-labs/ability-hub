<?php

namespace App\Actions\Users;

use App\Models\RegistrationCode;
use Illuminate\Support\Str;

class CreateRegistrationCodeAction
{
    public function execute(string $email): RegistrationCode
    {
        return RegistrationCode::create([
            'email' => $email,
            'code' => Str::numbers(rand(100000,999999))
        ]);
    }
}

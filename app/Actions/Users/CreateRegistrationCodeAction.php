<?php

namespace App\Actions\Users;

use App\Models\RegistrationCode;
use App\Notifications\RegistrationCodeCreated;
use Illuminate\Support\Str;

class CreateRegistrationCodeAction
{
    public function execute(string $email): RegistrationCode
    {
        $code = RegistrationCode::create([
            'email' => $email,
            'code' => Str::numbers(rand(100000,999999))
        ]);

        $code->notify(new RegistrationCodeCreated($code));
        $code->update(['sent_at' => now()]);

        return $code;
    }
}

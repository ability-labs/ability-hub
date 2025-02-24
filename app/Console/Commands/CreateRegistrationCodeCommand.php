<?php

namespace App\Console\Commands;

use App\Actions\Users\CreateRegistrationCodeAction;
use Illuminate\Console\Command;

class CreateRegistrationCodeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'registration:code {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(CreateRegistrationCodeAction $createRegistrationCode)
    {
        $email = $this->argument('email');
        $this->info("Creating registration code for: $email");
        $code = $createRegistrationCode->execute($email);
        $this->info("Code Created: " . $code->code);
    }
}

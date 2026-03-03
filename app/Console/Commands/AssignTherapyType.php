<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\AppointmentType;
use Illuminate\Console\Command;

class AssignTherapyType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:assign-therapy-type';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assegna la tipologia "Terapia" a tutti gli appuntamenti esistenti';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = AppointmentType::where('name->it', 'Terapia')->first();

        if (!$type) {
            $this->error('Tipologia "Terapia" non trovata nel database.');
            return Command::FAILURE;
        }

        $count = Appointment::query()->count();
        
        if ($count === 0) {
            $this->info('Non ci sono appuntamenti da aggiornare.');
            return Command::SUCCESS;
        }

        Appointment::query()->update(['appointment_type_id' => $type->id]);

        $this->info("Aggiornati {$count} appuntamenti con la tipologia '{$type->name}'.");

        return Command::SUCCESS;
    }
}

<?php

namespace Database\Seeders;

use App\Models\Learner;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Discipline;
use App\Models\Operator;
use App\Models\Appointment;
use Carbon\Carbon;

class FakeDataSeeder extends Seeder
{
    public function run()
    {
        // Creazione di un utente con credenziali fisse
        $user = User::factory()->create([
            'name'     => 'Seeded User',
            'email'    => 'seeded@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->command->info("User created: email: seeded@example.com, password: password");

        // Recupera tutte le discipline presenti nel sistema
        $disciplines = Discipline::all();

        if ($disciplines->isEmpty()) {
            $this->command->info("No disciplines found. Please seed the disciplines first.");
            return;
        }

        // Definizione degli slot orari: ciascuno di 1h30
        // (09:00-10:30, 10:30-12:00, 12:00-13:30, 14:30-16:00, 16:00-17:30, 17:30-19:00, 19:00-20:30)
        $slots = [
            ['09:00:00', '10:30:00'],
            ['10:30:00', '12:00:00'],
            ['12:00:00', '13:30:00'],
            ['14:30:00', '16:00:00'],
            ['16:00:00', '17:30:00'],
            ['17:30:00', '19:00:00'],
            ['19:00:00', '20:30:00'],
        ];

        // Calcola l'inizio e la fine del mese corrente
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth   = Carbon::now()->endOfMonth();

        // Itera su ogni giorno del mese corrente
        $date = $startOfMonth->copy();
        while ($date->lte($endOfMonth)) {
            // Considera solo i giorni dal lunedì al sabato (0 = Domenica in Carbon)
            if ($date->dayOfWeek !== Carbon::SUNDAY) {
                // Per ogni disciplina del sistema...
                foreach ($disciplines as $discipline) {
                    // Crea un operatore per l'utente tramite factory
                    $operator = Operator::factory()->for($user)->create();
                    // Assegna la disciplina corrente all'operatore (relazione many-to-many)
                    $operator->disciplines()->attach($discipline->id);
                    $learner = Learner::factory()->for($user)->create();

                    // Per ogni slot orario, crea un appuntamento
                    foreach ($slots as $slot) {
                        // Costruisci gli orari combinando la data corrente e l'orario dello slot
                        $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $slot[0]);
                        $endTime   = Carbon::parse($date->format('Y-m-d') . ' ' . $slot[1]);

                        // Crea l'appuntamento con Appointment::factory()
                        // Utilizziamo ->for($operator) per associare l'operatore e lasciare che la factory crei il learner
                        Appointment::factory()
                            ->for($operator) // Imposta operator_id
                            ->for($user, 'user') // Imposta user_id
                            ->for($learner, 'learner') // Imposta user_id
                            ->create([
                                'starts_at'     => $startTime,
                                'ends_at'       => $endTime,
                                'discipline_id' => $discipline->id,
                                'title'         => $learner->full_name . ' (' . $operator->name . ')',
                            ]);
                    }
                }
            }
            $date->addDay();
        }
    }
}

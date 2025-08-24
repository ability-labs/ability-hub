<?php

namespace Database\Seeders;

use Faker\Factory;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Discipline;
use App\Models\Operator;
use App\Models\Learner;
use App\Models\Appointment;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;

class FakeDataSeeder extends Seeder
{
    private function getCurrentMonthWeekDates($position): array
    {
        $start = CarbonImmutable::parse("$position Monday of this month");
        $end = $start->addDays(4);
        return [$start, $end];
    }

    public function run()
    {
        $faker =app(Factory::class)->create();
        // Recupera tutte le discipline presenti nel sistema
        $disciplines = Discipline::all();
        if ($disciplines->isEmpty()) {
            $this->command->info("No disciplines found. Please seed the disciplines first.");
            return;
        }

        // Creazione di un utente con credenziali fisse
        $user_email = 'seeded@example.com';
        $user_password = "password";
        $user = User::factory()->create([
            'name'     => 'Seeded User',
            'email'    => $user_email,
            'password' => bcrypt($user_password),
        ]);
        $this->command->info("User created: email: $user_email, password: $user_password");

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

        // Raccolta delle date di inizio e fine delle 4 settimane del mese corrente
        $weeks = collect([
            $this->getCurrentMonthWeekDates('first'),
            $this->getCurrentMonthWeekDates('second'),
            $this->getCurrentMonthWeekDates('third'),
            $this->getCurrentMonthWeekDates('fourth'),
        ])->map(fn (array $date) => ['start' => $date[0], 'end' => $date[1]]);

        // Creiamo 2 operatori per ogni disciplina e assegniamo la disciplina a ciascun operatore
        $disciplines->each(function (Discipline $discipline) use ($user) {
            $operators = Operator::factory()
                ->for($user)
                ->count(5)
                ->create();
            $operators->each(function (Operator $operator) use ($discipline) {
                $operator->disciplines()->attach($discipline->id);
            });
        });

        // Recupera tutti gli operatori dell'utente
        $allOperators = Operator::where('user_id', $user->id)->get();

        // Inizializziamo un array di scheduling per operatori (per evitare doppie prenotazioni nello stesso slot)
        $operatorSchedule = [];
        foreach ($allOperators as $operator) {
            $operatorSchedule[$operator->id] = [];
            Learner::factory()->for($operator)->for($user)->count(3)->create();
        }
//
//        // Creiamo 20 Studenti per l'utente
//        $learners = Learner::factory()->for($user)->count(50)->create();

        // Inizializziamo un array per lo scheduling dei learner (per evitare due appuntamenti nello stesso giorno)
        $learnerSchedule = [];

//        // Iteriamo tutte le settimane per ciascun learner
//        $learners->each(function (Learner $learner) use ($faker, $weeks, $disciplines, $slots, $user, $allOperators, &$operatorSchedule, &$learnerSchedule) {
//
//            $this->command->info("Creating Fake Appointments for " . $learner->full_name);
//            // Inizializza lo scheduling per il learner
//            $learnerSchedule[$learner->id] = [];
//            // Per ogni settimana del mese
//            foreach ($weeks as  $week) {
//                $weekStart =  $week['start'];
//                $weekEnd   = $week['end'];
//                $days = [];
//                // Creiamo un array di date per la settimana (dal lunedì al sabato)
//                for ($d = $weekStart->copy(); $d->lte($weekEnd); $d = $d->addDay()) {
//                    $days[] = $d->copy();
//                }
//                // Per ogni disciplina
//                foreach ($disciplines as $discipline) {
//                    // Recupera gli operatori per la disciplina tra quelli dell'utente
//                    $availableOperators = $allOperators->filter(function($op) use ($discipline) {
//                        return $op->disciplines->contains($discipline->id);
//                    });
//                    if ($availableOperators->isEmpty()) {
//                        $this->command->warn("No operators found for discipline {$discipline->slug}. Skipping appointments for this discipline.");
//                        continue;
//                    }
//                    $appointmentsNeeded = 3; // 3 appuntamenti per disciplina durante la settimana
//                    // Seleziona 3 giorni distinti dalla settimana in cui programmare gli appuntamenti
//                    $shuffledDays = $days;
//                    shuffle($shuffledDays);
//                    $selectedDays = array_slice($shuffledDays, 0, $appointmentsNeeded);
//
//                    // Per ogni appuntamento da creare, scegliamo uno slot casuale tra quelli disponibili
//                    foreach ($selectedDays as $day) {
//                        $dayKey = $day->toDateString();
//                        // Controlla che lo studente non abbia già un appuntamento in questo giorno
//                        if (isset($learnerSchedule[$learner->id][$dayKey])) {
//                           // Learner already has an appointment on   Skipping.
//                            continue;
//                        }
//                        // Cerca un operatore disponibile per questa disciplina e per uno slot casuale del giorno
//                        $operatorFound = null;
//                        $chosenSlotIndex = null;
//                        $chosenSlot = null;
//                        foreach ($availableOperators as $operator) {
//                            $opId = $operator->id;
//                            if (!isset($operatorSchedule[$opId][$dayKey])) {
//                                $operatorSchedule[$opId][$dayKey] = [];
//                            }
//                            // Crea una copia dello slot array e lo mescola per scegliere in modo casuale
//                            $shuffledSlots = $slots;
//                            shuffle($shuffledSlots);
//                            foreach ($shuffledSlots as $slot) {
//                                // Trova l'indice originale dello slot
//                                $slotIndex = array_search($slot, $slots);
//                                if (!in_array($slotIndex, $operatorSchedule[$opId][$dayKey])) {
//                                    $operatorFound = $operator;
//                                    $chosenSlotIndex = $slotIndex;
//                                    $chosenSlot = $slot;
//                                    break;
//                                }
//                            }
//                            if ($operatorFound) break;
//                        }
//                        if (!$operatorFound) {
//                            $this->command->warn("No available operator for discipline {$discipline->slug} on {$dayKey} for learner {$learner->full_name}. Appointment skipped.");
//                            continue;
//                        }
//                        // Imposta orari combinando la data corrente con l'orario dello slot scelto
//                        $startTime = Carbon::parse($day->format('Y-m-d') . ' ' . $chosenSlot[0]);
//                        $endTime   = Carbon::parse($day->format('Y-m-d') . ' ' . $chosenSlot[1]);
//
//                        // Crea l'appuntamento
//                        $title = $learner->full_name . ' (' . $operatorFound->name . ') - ' . strtoupper($discipline->slug);
//                        $appointment_attributes = [
//                            'starts_at' => $startTime,
//                            'ends_at' => $endTime,
//                            'discipline_id' => $discipline->id,
//                            'title' => $title,
//                            'comments' => 'Seeded appointment for discipline ' . $discipline->slug,
//                        ];
//
//                        if ($startTime->isPast()) {
//                            $appointment_attributes['operator_signed_at'] = $endTime;
//                             // leraners signs randomly
//                            if ($faker->boolean(75))
//                                $appointment_attributes['learner_signed_at'] = $endTime;
//                        }
//
//                        Appointment::factory()
//                            ->for($operatorFound)   // Imposta operator_id
//                            ->for($user, 'user')    // Imposta user_id
//                            ->for($learner, 'learner') // Imposta learner_id
//                            ->create($appointment_attributes);
//                        // Segna che lo studente ha un appuntamento in questo giorno
//                        $learnerSchedule[$learner->id][$dayKey] = true;
//                        // Segna lo slot come occupato per questo operatore
//                        $operatorSchedule[$operatorFound->id][$dayKey][] = $chosenSlotIndex;
//                    }
//                }
//            }
//        });
    }
}

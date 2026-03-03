<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AppointmentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['it' => 'Terapia', 'en' => 'Therapy'],
            ['it' => 'Supervisione', 'en' => 'Supervision'],
            ['it' => 'Workshop', 'en' => 'Workshop'],
        ];

        foreach ($types as $name) {
            \App\Models\AppointmentType::updateOrCreate(
                ['name->it' => $name['it']],
                ['name' => $name]
            );
        }
    }
}

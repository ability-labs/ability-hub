<?php

namespace Database\Seeders;

use App\Models\Discipline;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SlotsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $discipline_availabilities = collect(json_decode(file_get_contents(database_path('datasets/availabilities.json')),true));

        foreach ($discipline_availabilities as $discipline) {
            $db_discipline = Discipline::whereSlug($discipline['discipline'])->first();
            if ($db_discipline) {
                $slots = $discipline['slots'];
                foreach ($slots as $slot) {
                    $db_discipline->slots()->create($slot);
                }
            }
        }
    }
}

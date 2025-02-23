<?php

namespace Database\Seeders;

use App\Models\Discipline;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DisciplineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $disciplines = collect(json_decode(file_get_contents(database_path('datasets/disciplines.json')),true));

        $disciplines->each(fn (array $disciplineAttributes) => Discipline::create($disciplineAttributes));
    }
}

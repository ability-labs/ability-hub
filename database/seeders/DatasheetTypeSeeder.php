<?php

namespace Database\Seeders;

use App\Models\DatasheetType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatasheetTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect(json_decode(
            file_get_contents(database_path('datasets/datasheets.json')),
            true
        ))
            ->each(
                fn (array $type) => DatasheetType::create($type)
            );
    }
}

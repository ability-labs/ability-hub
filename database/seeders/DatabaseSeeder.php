<?php

namespace Database\Seeders;

use App\Models\ContentCard;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(DisciplineSeeder::class);
        $this->call(ReinforcerSeeder::class);
        $this->call(ContentCardSeeder::class);
        $this->call(DatasheetTypeSeeder::class);
    }
}

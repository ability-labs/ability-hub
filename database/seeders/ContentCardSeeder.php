<?php

namespace Database\Seeders;

use App\Models\ContentCard;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContentCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataset = collect(json_decode(
            file_get_contents(database_path('datasets/welcome_page-cards.json'))
            ,
        true));
        $dataset->each(fn (array $attributes) => ContentCard::create($attributes));
    }
}

<?php

namespace Database\Seeders;

use App\Models\Reinforcer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReinforcerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataset = json_decode(
            file_get_contents(database_path('datasets/reinforcers.json')),
        true
        );
        collect($dataset['categories'])
            ->each(function (array $category) {
                collect($category['subcategories'])
                    ->each(function (array $subcategory) use ($category) {
                        collect($subcategory['items'])
                            ->each(function (array $reinforcer) use ($subcategory, $category) {
                                Reinforcer::create(array_merge(
                                    $reinforcer,
                                    [
                                        'category' => $category['name'],
                                        'subcategory' => $subcategory['name']
                                    ]
                                ));
                            });
                    });
            });
    }
}

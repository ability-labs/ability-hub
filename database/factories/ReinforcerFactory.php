<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reinforcer>
 */
class ReinforcerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => $this->faker->slug(),
            'category' => [
                'en' => $this->faker->word()
            ],
            'subcategory' => [
                'en' => $this->faker->word()
            ],
            'name' => [
                'en' => $this->faker->word()
            ],
        ];
    }
}

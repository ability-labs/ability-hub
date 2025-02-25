<?php

namespace Database\Factories;

use App\Models\Learner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Datasheet>
 */
class DatasheetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => $this->faker->word(),
            'learner_id' => Learner::factory(),
            'operator_id' => Learner::factory(),

            'data' => [],

            'finalized_at' => $this->faker->word(),
        ];
    }
}

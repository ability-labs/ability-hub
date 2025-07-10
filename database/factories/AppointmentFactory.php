<?php

namespace Database\Factories;

use App\Models\Discipline;
use App\Models\Learner;
use App\Models\Operator;
use App\Models\User;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $starts_at = $this->faker->dateTimeThisMonth();
        $ends_at = $starts_at->add(CarbonInterval::hours($this->faker->numberBetween(1,3)));
        return [
            'learner_id' => Learner::factory(),
            'operator_id' => Operator::factory(),
            'title' => $this->faker->sentence(),
            'discipline_id' => Discipline::factory(),    // <<< aggiunto
            'user_id'       => User::factory(),          // <<< aggiunto
            'starts_at' => $starts_at,
            'ends_at' => $ends_at,
            'comments' => $this->faker->text(),
            'duration_minutes' => $this->faker->numberBetween(30,180),
        ];
    }
}

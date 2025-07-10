<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Slot>
 */
class SlotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startHour = $this->faker->numberBetween(8, 18);
        $startMinute = $this->faker->randomElement([0, 30]);
        $duration = $this->faker->randomElement([30, 60, 90, 120]);

        $totalStartMinutes = $startHour * 60 + $startMinute;
        $totalEndMinutes = $totalStartMinutes + $duration;
        $endHour = intdiv($totalEndMinutes, 60);
        $endMinute = $totalEndMinutes % 60;

        return [
            'week_day' => $this->faker->numberBetween(1, 5), // Lun-Ven
            'day_span' => $this->faker->randomElement(['Morning', 'Afternoon', 'Evening']),
            'start_time_hour' => $startHour,
            'start_time_minute' => $startMinute,
            'end_time_hour' => $endHour,
            'end_time_minute' => $endMinute,
            'duration_minutes' => $duration,
        ];
    }
}

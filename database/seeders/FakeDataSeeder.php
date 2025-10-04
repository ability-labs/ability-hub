<?php

namespace Database\Seeders;

use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use App\Models\User;
use App\Models\Discipline;
use App\Models\Operator;
use App\Models\Learner;

class FakeDataSeeder extends Seeder
{
    public function run()
    {
        $faker = app(Factory::class)->create();

        $disciplines = Discipline::all();
        if ($disciplines->isEmpty()) {
            $this->command->info('No disciplines found. Please seed the disciplines first.');
            return;
        }

        $abaDiscipline = $disciplines->firstWhere('slug', 'aba');
        if (!$abaDiscipline instanceof Discipline) {
            $this->command->warn('ABA discipline not found. Run DisciplineSeeder before FakeDataSeeder.');
            return;
        }

        $abaSlots = $abaDiscipline->slots()->get();
        if ($abaSlots->isEmpty()) {
            $this->command->warn('No slots found for ABA discipline. Run SlotsSeeder before FakeDataSeeder.');
            return;
        }

        $slotMatrix = $this->groupSlotsByDaySpan($abaSlots);

        $userEmail = 'seeded@example.com';
        $userPassword = 'password';
        $user = User::factory()->create([
            'name'     => 'Seeded User',
            'email'    => $userEmail,
            'password' => bcrypt($userPassword),
        ]);
        $this->command->info("User created: email: $userEmail, password: $userPassword");

        [$partTimeOperators, $fullTimeOperators] = $this->createOperatorsWithAvailability(
            $user,
            $abaDiscipline,
            $slotMatrix,
            $disciplines,
            $faker
        );

        $operators = Operator::with('slots')
            ->whereIn('id', $partTimeOperators->pluck('id')->merge($fullTimeOperators->pluck('id')))
            ->get();

        $learners = Learner::factory()
            ->for($user)
            ->count(18)
            ->create();

        $learners->each(function (Learner $learner) use ($faker, $abaSlots, $operators) {
            $this->assignLearnerAvailability($learner, $abaSlots, $operators, $faker);
        });

        $this->command->info(sprintf(
            'Seeded %d learners with multi-operator assignments, %d part-time operators and %d full-time operators dedicated to ABA.',
            $learners->count(),
            $partTimeOperators->count(),
            $fullTimeOperators->count()
        ));
    }

    private function groupSlotsByDaySpan(Collection $slots): Collection
    {
        return $slots
            ->groupBy('week_day')
            ->map(fn (Collection $daySlots) => $daySlots->groupBy('day_span'));
    }

    private function createOperatorsWithAvailability(
        User $user,
        Discipline $abaDiscipline,
        Collection $slotMatrix,
        Collection $allDisciplines,
        Generator $faker
    ): array {
        $partTimeOperators = Operator::factory()
            ->for($user)
            ->count(6)
            ->create();

        $fullTimeOperators = Operator::factory()
            ->for($user)
            ->count(4)
            ->create();

        $partTimeOperators->each(function (Operator $operator) use ($abaDiscipline, $slotMatrix, $faker, $allDisciplines) {
            $this->attachOperatorDisciplines($operator, $abaDiscipline, $allDisciplines, $faker);
            $this->assignPartTimeSlots($operator, $slotMatrix, $faker);
        });

        $fullTimeOperators->each(function (Operator $operator) use ($abaDiscipline, $slotMatrix, $faker, $allDisciplines) {
            $this->attachOperatorDisciplines($operator, $abaDiscipline, $allDisciplines, $faker);
            $this->assignFullTimeSlots($operator, $slotMatrix, $faker);
        });

        return [$partTimeOperators, $fullTimeOperators];
    }

    private function attachOperatorDisciplines(
        Operator $operator,
        Discipline $abaDiscipline,
        Collection $allDisciplines,
        Generator $faker
    ): void {
        $operator->disciplines()->syncWithoutDetaching([$abaDiscipline->id]);

        $otherDisciplineIds = $allDisciplines
            ->filter(fn (Discipline $discipline) => $discipline->id !== $abaDiscipline->id)
            ->pluck('id');

        if ($otherDisciplineIds->isNotEmpty()) {
            $extra = $otherDisciplineIds
                ->shuffle()
                ->take($faker->numberBetween(0, min(2, $otherDisciplineIds->count())));

            if ($extra->isNotEmpty()) {
                $operator->disciplines()->syncWithoutDetaching($extra->all());
            }
        }
    }

    private function assignPartTimeSlots(Operator $operator, Collection $slotMatrix, Generator $faker): void
    {
        $pairs = $this->buildDaySpanPairs($slotMatrix);
        if ($pairs->isEmpty()) {
            return;
        }

        $morningPairs = $pairs->filter(fn (array $pair) => $pair['span'] === 'Morning');
        $afternoonPairs = $pairs->filter(fn (array $pair) => $pair['span'] === 'Afternoon');
        $eveningPairs = $pairs->filter(fn (array $pair) => $pair['span'] === 'Evening');

        $selectedPairs = collect();

        if ($morningPairs->isNotEmpty()) {
            $selectedPairs = $selectedPairs->concat(
                $this->pickPairs($morningPairs, 2, 3, $faker)
            );
        }

        if ($afternoonPairs->isNotEmpty()) {
            $selectedPairs = $selectedPairs->concat(
                $this->pickPairs($afternoonPairs, 2, 3, $faker)
            );
        }

        if ($selectedPairs->count() < 4 && $eveningPairs->isNotEmpty()) {
            $selectedPairs = $selectedPairs->concat(
                $this->pickPairs($eveningPairs, 1, min(2, $eveningPairs->count()), $faker)
            );
        }

        $targetHalfDays = $faker->numberBetween(4, 6);

        if ($selectedPairs->count() < $targetHalfDays) {
            $remainingPairs = $pairs->reject(function (array $pair) use ($selectedPairs) {
                return $selectedPairs->contains(fn (array $selected) =>
                    $selected['day'] === $pair['day'] && $selected['span'] === $pair['span']
                );
            });

            $selectedPairs = $selectedPairs->concat(
                $remainingPairs->shuffle()->take($targetHalfDays - $selectedPairs->count())
            );
        }

        $finalPairs = $selectedPairs
            ->unique(fn (array $pair) => $pair['day'] . '-' . $pair['span'])
            ->take($targetHalfDays);

        $this->attachSlotsFromPairs($operator, $slotMatrix, $finalPairs);
    }

    private function assignFullTimeSlots(Operator $operator, Collection $slotMatrix, Generator $faker): void
    {
        $days = $slotMatrix->keys();
        if ($days->isEmpty()) {
            return;
        }

        $dayOff = $days->random();

        $slotMatrix->each(function (Collection $spans, int $day) use ($operator, $dayOff) {
            if ($day === $dayOff) {
                return;
            }

            $spans->each(function (Collection $slots) use ($operator) {
                $operator->slots()->syncWithoutDetaching($slots->pluck('id')->all());
            });
        });
    }

    private function buildDaySpanPairs(Collection $slotMatrix): Collection
    {
        return $slotMatrix->flatMap(function (Collection $spans, int $day) {
            return $spans->keys()->map(fn (string $span) => [
                'day' => $day,
                'span' => $span,
            ]);
        })->values();
    }

    private function pickPairs(Collection $pairs, int $min, int $max, Generator $faker): Collection
    {
        if ($pairs->isEmpty()) {
            return collect();
        }

        $max = min($max, $pairs->count());
        $min = min($min, $max);

        if ($max <= 0) {
            return collect();
        }

        if ($min <= 0) {
            $min = 1;
        }

        $count = $min === $max ? $min : $faker->numberBetween($min, $max);

        return $pairs->shuffle()->take($count)->values();
    }

    private function attachSlotsFromPairs(Operator $operator, Collection $slotMatrix, Collection $pairs): void
    {
        $pairs->each(function (array $pair) use ($operator, $slotMatrix) {
            /** @var Collection|null $spans */
            $spans = $slotMatrix->get($pair['day']);
            if (!$spans instanceof Collection) {
                return;
            }

            /** @var Collection|null $slots */
            $slots = $spans->get($pair['span']);

            if ($slots instanceof Collection && $slots->isNotEmpty()) {
                $operator->slots()->syncWithoutDetaching($slots->pluck('id')->all());
            }
        });
    }

    private function assignLearnerAvailability(
        Learner $learner,
        Collection $abaSlots,
        Collection $operators,
        Generator $faker
    ): void {
        $requiredSlots = max(1, (int) ceil($learner->weekly_minutes / 90));
        $extraSlots = $faker->boolean(40) ? 1 : 0;

        $selectedSlots = $abaSlots
            ->shuffle()
            ->take($requiredSlots + $extraSlots);

        if ($selectedSlots->count() < $requiredSlots) {
            $selectedSlots = $abaSlots->shuffle()->take($requiredSlots);
        }

        $learner->slots()->sync($selectedSlots->pluck('id')->all());

        $targetOperators = min(max(2, $faker->numberBetween(2, 3)), $operators->count());

        $operatorsWithOverlap = $operators->filter(function (Operator $operator) use ($selectedSlots) {
            return $operator->slots
                ->pluck('id')
                ->intersect($selectedSlots->pluck('id'))
                ->isNotEmpty();
        });

        $selectedOperators = $operatorsWithOverlap->shuffle()->take($targetOperators);

        if ($selectedOperators->count() < 2) {
            $additional = $operators
                ->diff($selectedOperators)
                ->shuffle()
                ->take(2 - $selectedOperators->count());

            $selectedOperators = $selectedOperators->concat($additional);
        }

        $learner->operators()->sync(
            $selectedOperators
                ->unique(fn (Operator $operator) => $operator->id)
                ->pluck('id')
                ->all()
        );
    }
}

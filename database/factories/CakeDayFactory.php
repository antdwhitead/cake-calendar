<?php

namespace Database\Factories;

use App\Enums\CakeType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CakeDay>
 */
class CakeDayFactory extends Factory
{
    public function definition(): array
    {
        return [
            'date' => fake()->dateTimeBetween('now', '+1 year'),
            'cake_type' => fake()->randomElement(CakeType::cases()),
            'names' => [fake()->name()],
        ];
    }

    public function small(): static
    {
        return $this->state(['cake_type' => CakeType::Small]);
    }

    public function large(): static
    {
        return $this->state(['cake_type' => CakeType::Large]);
    }

    public function withNames(array $names): static
    {
        return $this->state(['names' => $names]);
    }
}

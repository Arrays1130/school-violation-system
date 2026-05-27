<?php

namespace Database\Factories;

use App\Models\Violation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Violation>
 */
class ViolationFactory extends Factory
{
    protected $model = Violation::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('VIO-###')),
            'title' => fake()->sentence(3),
            'category' => 'Conduct',
            'severity' => 'Minor',
            'default_description' => fake()->sentence(),
            'first_offense' => 'Verbal warning',
            'second_offense' => 'Written warning',
            'third_offense' => 'Parent conference',
        ];
    }

    public function major(): static
    {
        return $this->state(fn () => ['severity' => 'Major']);
    }
}

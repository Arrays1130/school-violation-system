<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\StudentCase;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentCase>
 */
class StudentCaseFactory extends Factory
{
    protected $model = StudentCase::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'violation_id' => Violation::factory(),
            'description' => fake()->paragraph(),
            'witness' => fake()->name(),
            'occurred_at' => now(),
            'offense_level' => 1,
            'sanction' => 'Verbal warning',
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (StudentCase $case) {
            $case->forceFill([
                'status' => $case->status ?? 'Pending',
                'created_by' => $case->created_by ?? User::factory()->create()->id,
            ]);
        });
    }

    public function endorsed(): static
    {
        return $this->afterMaking(function (StudentCase $case) {
            $case->forceFill(['endorsed_at' => now()]);
        });
    }

    public function closed(): static
    {
        return $this->afterMaking(function (StudentCase $case) {
            $userId = User::factory()->create()->id;
            $case->forceFill([
                'status' => 'Closed',
                'closed_at' => now(),
                'closed_by' => $userId,
            ]);
        });
    }
}

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
            'status' => 'Pending',
            'created_by' => User::factory(),
            'offense_level' => 1,
            'sanction' => 'Verbal warning',
        ];
    }
}

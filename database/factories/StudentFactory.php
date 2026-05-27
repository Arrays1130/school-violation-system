<?php

namespace Database\Factories;

use App\Models\Student;
use App\Support\DepartmentResolver;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        $department = DepartmentResolver::shortcutToLong('CCE');

        return [
            'full_name' => fake()->name(),
            'section' => 'A',
            'year_level' => '1',
            'department' => $department,
            'email' => fake()->unique()->safeEmail(),
            'guardian_name' => fake()->name(),
            'guardian_email' => fake()->safeEmail(),
            'guardian_phone' => fake()->phoneNumber(),
            'password' => Hash::make('password'),
            'password_changed_at' => now(),
        ];
    }

    public function inDepartment(string $shortcut): static
    {
        return $this->state(fn () => [
            'department' => DepartmentResolver::shortcutToLong($shortcut),
        ]);
    }
}

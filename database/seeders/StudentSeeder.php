<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = ['BSIT', 'BSCS', 'BSBA', 'BSED', 'BSHRM'];
        $sections = ['1-A', '1-B', '2-A', '3-A', '4-A', '4-B'];

        // Create 50 random students
        for ($i = 0; $i < 50; $i++) {
            Student::create([
                'full_name' => fake()->name(),
                'section' => fake()->randomElement($sections),
                'department' => fake()->randomElement($departments),
                'email' => fake()->unique()->safeEmail(),
            ]);
        }
    }
}

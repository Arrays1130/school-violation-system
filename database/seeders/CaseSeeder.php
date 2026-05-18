<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Violation;
use App\Models\StudentCase;
use App\Models\User;

class CaseSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::all();
        $violations = Violation::all();
        $admin = User::first();

        // Create 80 random cases
        for ($i = 0; $i < 80; $i++) {
            $student = $students->random();
            $violation = $violations->random();
            $date = fake()->dateTimeBetween('-3 months', 'now');
            
            // Logic for status based on severity/randomness
            $status = fake()->randomElement(['Pending', 'Pending', 'Closed', 'Hearing Scheduled']); // Weighted towards Pending
            if ($violation->severity == 'Major') $status = 'Hearing Scheduled'; // Major offenses often scheduled
            
            // Randomly close some past cases
            if ($date < now()->subMonth()) $status = 'Closed';

            StudentCase::create([
                'student_id' => $student->id,
                'violation_id' => $violation->id,
                'description' => $violation->default_description . ' ' . fake()->sentence(),
                'occurred_at' => $date,
                'status' => $status,
                'created_by' => $admin->id,
            ]);
        }
    }
}

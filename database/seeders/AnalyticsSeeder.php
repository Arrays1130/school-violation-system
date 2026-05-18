<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Violation;
use App\Models\StudentCase;
use Carbon\Carbon;

class AnalyticsSeeder extends Seeder
{
    public function run()
    {
        // ensuring we have students and violations
        if (Student::count() < 5) {
            $this->command->info('Creating dummy students...');
            Student::create(['student_id' => '2023-0001', 'first_name' => 'John', 'last_name' => 'Doe', 'department' => 'BSIT', 'course' => 'BSIT', 'year_level' => '1', 'gender' => 'Male']);
            Student::create(['student_id' => '2023-0002', 'first_name' => 'Jane', 'last_name' => 'Smith', 'department' => 'BSBA', 'course' => 'BSBA', 'year_level' => '2', 'gender' => 'Female']);
            Student::create(['student_id' => '2023-0003', 'first_name' => 'Mike', 'last_name' => 'Johnson', 'department' => 'BSCrim', 'course' => 'BSCrim', 'year_level' => '3', 'gender' => 'Male']);
            Student::create(['student_id' => '2023-0004', 'first_name' => 'Sarah', 'last_name' => 'Williams', 'department' => 'BSED', 'course' => 'BSED', 'year_level' => '4', 'gender' => 'Female']);
            Student::create(['student_id' => '2023-0005', 'first_name' => 'Chris', 'last_name' => 'Brown', 'department' => 'BSHRM', 'course' => 'BSHRM', 'year_level' => '1', 'gender' => 'Male']);
        }

        if (Violation::count() == 0) {
           $this->command->info('Creating dummy violations...');
           Violation::create(['code' => 'V-001', 'title' => 'Incomplete Uniform', 'category' => 'Minor', 'severity' => 'Minor']);
           Violation::create(['code' => 'V-002', 'title' => 'Littering', 'category' => 'Minor', 'severity' => 'Minor']);
           Violation::create(['code' => 'V-003', 'title' => 'Cutting Classes', 'category' => 'Major', 'severity' => 'Major']);
           Violation::create(['code' => 'V-004', 'title' => 'Smoking/Vaping', 'category' => 'Major', 'severity' => 'Major']);
           Violation::create(['code' => 'V-005', 'title' => 'Vandalism', 'category' => 'Serious', 'severity' => 'Serious']);
        }

        $students = Student::all();
        $violations = Violation::all();

        $this->command->info('Seeding 50 random cases...');

        for ($i = 0; $i < 50; $i++) {
            $date = Carbon::now()->subDays(rand(0, 180)); // Last 6 months
            
            StudentCase::create([
                'student_id' => $students->random()->id,
                'violation_id' => $violations->random()->id,
                'description' => 'Auto-generated test case for analytics.',
                'occurred_at' => $date,
                'created_at' => $date, // Important for the trend chart
                'updated_at' => $date,
                'status' => 'Resolved', 
                'offense_level' => rand(1, 3),
                'created_by' => 1 
            ]);
        }
        
        $this->command->info('Analytics data planted!');
    }
}

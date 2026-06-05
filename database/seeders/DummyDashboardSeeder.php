<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use App\Models\Violation;
use App\Models\StudentCase;
use App\Models\Hearing;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DummyDashboardSeeder extends Seeder
{
    public function run()
    {
        // 1. Ensure Admin
        $user = User::firstOrCreate(
            ['email' => 'admin@ilink.edu.ph'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'department' => 'Disciplinary Office'
            ]
        );

        // 2. Comprehensive Violations List
        $violationsData = [
            ['code' => 'MIN-01', 'title' => 'Improper Uniform', 'category' => 'Dress Code', 'severity' => 'Minor'],
            ['code' => 'MIN-02', 'title' => 'No ID worn inside campus', 'category' => 'Security', 'severity' => 'Minor'],
            ['code' => 'MIN-03', 'title' => 'Littering', 'category' => 'Environment', 'severity' => 'Minor'],
            ['code' => 'MIN-04', 'title' => 'Loitering during class hours', 'category' => 'Behavior', 'severity' => 'Minor'],
            ['code' => 'MAJ-01', 'title' => 'Cheating in Examination', 'category' => 'Academic Dishonesty', 'severity' => 'Major'],
            ['code' => 'MAJ-02', 'title' => 'Vandalism of School Property', 'category' => 'Destruction', 'severity' => 'Major'],
            ['code' => 'MAJ-03', 'title' => 'Smoking/Vaping inside campus', 'category' => 'Health & Safety', 'severity' => 'Major'],
            ['code' => 'MAJ-04', 'title' => 'Disrespect to Authority', 'category' => 'Behavior', 'severity' => 'Major'],
            ['code' => 'CRI-01', 'title' => 'Bullying / Harassment', 'category' => 'Behavior', 'severity' => 'Major'],
            ['code' => 'CRI-02', 'title' => 'Bringing deadly weapons', 'category' => 'Security', 'severity' => 'Major'],
            ['code' => 'CRI-03', 'title' => 'Possession of illegal drugs', 'category' => 'Health & Safety', 'severity' => 'Major'],
            ['code' => 'CRI-04', 'title' => 'Physical Assault / Fighting', 'category' => 'Behavior', 'severity' => 'Major'],
        ];

        $violations = [];
        foreach ($violationsData as $v) {
            $violations[] = Violation::firstOrCreate(['code' => $v['code']], $v);
        }

        // 3. Create 20 Random Students
        $students = [];
        $departments = [
            'Bachelor Of Science In Criminology',
            'Bachelor Of Science In Information System',
            'Bachelor Of Technical Vocational Teachers Education',
            'College Of Business And Accounting Education'
        ];
        for ($i = 1; $i <= 20; $i++) {
            $students[] = Student::firstOrCreate(
                ['email' => "student{$i}@ilink.edu.ph"],
                [
                    'full_name' => fake()->name(),
                    'department' => fake()->randomElement($departments),
                    'section' => fake()->randomElement(['A', 'B', 'C']),
                    'password' => Hash::make('password')
                ]
            );
        }

        // 4. Create 50 Random Cases to populate the dashboard metrics
        $statuses = ['Open', 'Scheduled', 'Resolved', 'Dismissed'];
        for ($i = 0; $i < 50; $i++) {
            $status = fake()->randomElement($statuses);
            
            // Randomize past dates (up to 6 months ago) to show charts
            $occurredAt = Carbon::now()->subDays(rand(1, 180));
            $createdAt = (clone $occurredAt)->addDays(rand(1, 3));

            $case = StudentCase::create([
                'student_id' => fake()->randomElement($students)->id,
                'violation_id' => fake()->randomElement($violations)->id,
                'created_by' => $user->id,
                'status' => $status,
                'description' => fake()->sentence(10),
                'occurred_at' => $occurredAt,
                'created_at' => $createdAt,
                'updated_at' => $createdAt
            ]);

            // 5. If Scheduled or Resolved, create hearings
            if (in_array($status, ['Scheduled', 'Resolved'])) {
                $hearingDate = (clone $createdAt)->addDays(rand(2, 7));
                $hearingStatus = ($status === 'Resolved') ? 'completed' : 'scheduled';
                // If it's a scheduled case but hearing date is in past, push it to future so dashboard shows upcoming
                if ($status === 'Scheduled' && $hearingDate->isPast()) {
                    $hearingDate = Carbon::now()->addDays(rand(1, 14));
                }

                Hearing::create([
                    'case_id' => $case->id,
                    'scheduled_at' => $hearingDate->setHour(rand(9, 15))->setMinute(0),
                    'venue' => 'Discipline Office - Room ' . rand(101, 205),
                    'participants' => json_encode(['System Admin', $case->student->full_name, 'Counselor']),
                    'notes' => fake()->sentence(8)
                ]);
            }
        }
        
        echo "Comprehensive dummy data seeded successfully!\n";
    }
}

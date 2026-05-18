<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Violation;

class ViolationSeeder extends Seeder
{
    public function run(): void
    {
        $violations = [
            // Minor Offenses
            ['code' => 'V-001', 'title' => 'Improper Uniform', 'category' => 'Appearance', 'severity' => 'Minor', 'default_description' => 'Student was not wearing proper uniform.'],
            ['code' => 'V-002', 'title' => 'No ID', 'category' => 'Appearance', 'severity' => 'Minor', 'default_description' => 'Student entered campus without ID.'],
            ['code' => 'V-003', 'title' => 'Late Arrival', 'category' => 'Attendance', 'severity' => 'Minor', 'default_description' => 'Habitual tardiness.'],
            ['code' => 'V-004', 'title' => 'Luttering', 'category' => 'Conduct', 'severity' => 'Minor', 'default_description' => 'Improper disposal of trash.'],
            
            // Major Offenses
            ['code' => 'V-101', 'title' => 'Academic Dishonesty', 'category' => 'Academic', 'severity' => 'Major', 'default_description' => 'Cheating during examination.'],
            ['code' => 'V-102', 'title' => 'Vandalism (Minor)', 'category' => 'Conduct', 'severity' => 'Major', 'default_description' => 'Writing on desks or walls.'],
            ['code' => 'V-103', 'title' => 'Bullying', 'category' => 'Conduct', 'severity' => 'Major', 'default_description' => 'Verbal harassment of another student.'],
            ['code' => 'V-104', 'title' => 'Skipping Class', 'category' => 'Attendance', 'severity' => 'Major', 'default_description' => 'Cut classes without valid reason.'],

            // Critical Offenses (Mapped to Major per schema changes)
            ['code' => 'V-201', 'title' => 'Physical Assault', 'category' => 'Conduct', 'severity' => 'Major', 'default_description' => 'Physically harming another person.'],
            ['code' => 'V-202', 'title' => 'Possession of Alcohol/Drugs', 'category' => 'Conduct', 'severity' => 'Major', 'default_description' => 'Found in possession of prohibited substances.'],
            ['code' => 'V-203', 'title' => 'Theft', 'category' => 'Conduct', 'severity' => 'Major', 'default_description' => 'Stealing property.'],
            ['code' => 'V-204', 'title' => 'Forgery', 'category' => 'Academic', 'severity' => 'Major', 'default_description' => 'Falsifying official documents.'],
        ];

        foreach ($violations as $v) {
            Violation::create($v);
        }
    }
}

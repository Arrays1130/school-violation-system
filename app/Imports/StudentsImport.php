<?php

namespace App\Imports;

use App\Models\Student;
use App\Support\DepartmentResolver;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class StudentsImport implements ToCollection, WithHeadingRow
{
    /**
    * @param \Illuminate\Support\Collection $collection
    */
    public function collection(\Illuminate\Support\Collection $collection)
    {
        // dd('First Row Keys (Headers):', array_keys($firstRow->toArray()), 'First Row Data:', $firstRow->toArray());
        
        Log::info('Import collection start.');

        // Disable model events to prevent slow broadcast events/timeouts per row insertion
        $dispatcher = Student::getEventDispatcher();
        Student::unsetEventDispatcher();

        // Pre-hash the default password once to avoid expensive Bcrypt hashing on every single row
        $defaultPassword = config('school.student_default_password') ?: 'password123';
        $hashedPassword = Hash::make($defaultPassword);

        try {
            foreach ($collection as $row) {
                $rowArr = $row->toArray();
                
                // Handle Google Workspace/CSV headers (e.g., "First Name [Required]" -> first_name_required)
                $firstName = $rowArr['first_name_required'] ?? $rowArr['first_name'] ?? '';
                $lastName = $rowArr['last_name_required'] ?? $rowArr['last_name'] ?? '';
                
                // Construct full name
                $fullName = trim($firstName . ' ' . $lastName);
                
                // Allow 'full_name' column if present directly
                if (empty($fullName) && !empty($rowArr['full_name'])) {
                    $fullName = $rowArr['full_name'];
                }
     
                $email = $rowArr['email_address_required'] ?? $rowArr['email'] ?? null;
                $department = $rowArr['department'] ?? null;
                
                if ($department) {
                    $department = DepartmentResolver::shortcutToLong($department) ?? $department;
                }

                $yearLevel = $rowArr['year_level'] ?? null;
                $section = $rowArr['section'] ?? null;
     
                // Skip if required fields are missing
                if (empty($fullName) || empty($email)) {
                    Log::warning('Skipping row due to missing required fields:', ['row' => $rowArr]);
                    continue;
                }
     
                // Check if student already exists by email
                $student = Student::where('email', $email)->first();
     
                if ($student) {
                    // Log::info('Skipping duplicate student:', ['email' => $email]);
                    continue;
                }
     
                // Log::info('Creating new student:', ['email' => $email]);
     
                Student::create([
                    'full_name'     => $fullName,
                    'section'       => $section,
                    'year_level'    => $yearLevel,
                    'department'    => $department,
                    'email'         => $email,
                    'guardian_name' => $rowArr['guardian_name'] ?? null,
                    'guardian_email'=> $rowArr['guardian_email'] ?? null,
                    'guardian_phone'=> $rowArr['guardian_phone'] ?? null,
                    'password'      => $hashedPassword,
                    'password_changed_at' => null, // force reset if student auth is enabled
                ]);
            }
        } finally {
            // Restore the event dispatcher
            if ($dispatcher) {
                Student::setEventDispatcher($dispatcher);
            }
        }

        // Manually clear cache
        \App\Models\StudentCase::clearDashboardCache();
    }
}

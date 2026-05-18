<?php

namespace App\Imports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;
class StudentsImport implements ToCollection, WithHeadingRow
{
    /**
    * @param \Illuminate\Support\Collection $collection
    */
    public function collection(\Illuminate\Support\Collection $collection)
    {
        // dd('First Row Keys (Headers):', array_keys($firstRow->toArray()), 'First Row Data:', $firstRow->toArray());
        
        Log::info('Import collection start.');

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
            
            // Map shortcuts to long department names for storage
            $deptMapping = [
                'CEE' => 'Bachelor Of Science In Information System',
                'CCJE' => 'Bachelor Of Science In Criminology',
                'CTE' => 'Bachelor Of Technical Vocational Teachers Education',
                'CBAE' => 'College Of Business And Accounting Education',
                'BSIT' => 'Bachelor Of Science In Information System',
            ];
            
            if (isset($deptMapping[$department])) {
                $department = $deptMapping[$department];
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
                'password'      => \Illuminate\Support\Facades\Hash::make('ilink2026'),
            ]);
        }
    }
}

<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use App\Models\Violation;
use App\Support\StudentPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentImportPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_csv_import_uses_random_password_when_not_configured(): void
    {
        config(['school.student_default_password' => null]);

        $admin = User::factory()->admin()->create();

        $csv = "full_name,email,department,year_level,section\n";
        $csv .= "John Doe,john.doe@ilinkcst.edu.ph,CCE,1,A\n";

        $response = $this->actingAs($admin)->post(route('students.import'), [
            'file' => UploadedFile::fake()->createWithContent('students.csv', $csv),
        ]);

        $response->assertRedirect(route('students.index'));
        $student = Student::where('email', 'john.doe@ilinkcst.edu.ph')->first();
        $this->assertNotNull($student);
        $this->assertFalse(Hash::check('password123', $student->password));
        $this->assertSame('1st Year', $student->year_level);
    }

    public function test_csv_import_accepts_department_email_section_year_level_headers(): void
    {
        config(['school.student_default_password' => 'TempPass123!']);

        $admin = User::factory()->admin()->create();

        $csv = "Department,Email Address,Section,Year Level\n";
        $csv .= "CCE,maria.santos@ilinkcst.edu.ph,B,1st Year\n";

        $this->actingAs($admin)->post(route('students.import'), [
            'file' => UploadedFile::fake()->createWithContent('students.csv', $csv),
        ])
            ->assertRedirect(route('students.index'))
            ->assertSessionHas('success');

        $student = Student::where('email', 'maria.santos@ilinkcst.edu.ph')->first();
        $this->assertNotNull($student);
        $this->assertSame('Maria Santos', $student->full_name);
        $this->assertSame('B', $student->section);
        $this->assertSame('1st Year', $student->year_level);
        $this->assertSame('SY 2024-2025', $student->academic_year);
        $this->assertNotNull($student->department);
    }

    public function test_csv_import_uses_academic_year_column_when_provided(): void
    {
        $admin = User::factory()->admin()->create();

        $csv = "Department,Email Address,Section,Year Level,Academic Year\n";
        $csv .= "CCE,pedro.reyes@ilinkcst.edu.ph,A,2nd Year,SY 2023-2024\n";

        $this->actingAs($admin)->post(route('students.import'), [
            'file' => UploadedFile::fake()->createWithContent('students.csv', $csv),
        ])->assertRedirect(route('students.index'));

        $student = Student::where('email', 'pedro.reyes@ilinkcst.edu.ph')->first();
        $this->assertNotNull($student);
        $this->assertSame('SY 2023-2024', $student->academic_year);
    }

    public function test_csv_import_without_valid_rows_shows_error(): void
    {
        $admin = User::factory()->admin()->create();

        $csv = "Department,Section,Year Level\n";
        $csv .= "CCE,B,1st Year\n";

        $this->actingAs($admin)->post(route('students.import'), [
            'file' => UploadedFile::fake()->createWithContent('students.csv', $csv),
        ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_student_password_helper_generates_unique_hashes_without_config(): void
    {
        config(['school.student_default_password' => null]);

        $first = StudentPassword::hash();
        $second = StudentPassword::hash();

        $this->assertNotSame($first, $second);
    }
}

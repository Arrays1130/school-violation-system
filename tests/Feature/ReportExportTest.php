<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\StudentCase;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_export_csv_report(): void
    {
        $admin = User::factory()->admin()->create();
        $violation = Violation::factory()->create();
        $student = Student::factory()->inDepartment('CCE')->create();

        StudentCase::factory()->create([
            'student_id' => $student->id,
            'violation_id' => $violation->id,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('reports.csv', ['department' => $student->department]));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_dean_csv_export_is_department_scoped(): void
    {
        $dean = User::factory()->dean('CCE')->create();
        $violation = Violation::factory()->create();

        $cceStudent = Student::factory()->inDepartment('CCE')->create();
        $ccjeStudent = Student::factory()->inDepartment('CCJE')->create();

        StudentCase::factory()->create([
            'student_id' => $cceStudent->id,
            'violation_id' => $violation->id,
        ]);

        StudentCase::factory()->create([
            'student_id' => $ccjeStudent->id,
            'violation_id' => $violation->id,
        ]);

        $response = $this->actingAs($dean)->get(route('reports.csv'));

        $response->assertOk();
        $content = $response->streamedContent();
        $this->assertStringContainsString($cceStudent->full_name, $content);
        $this->assertStringNotContainsString($ccjeStudent->full_name, $content);
    }
}

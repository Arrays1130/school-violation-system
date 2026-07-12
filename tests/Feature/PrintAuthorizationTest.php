<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\StudentCase;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrintAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dean_cannot_print_case_outside_department(): void
    {
        $dean = User::factory()->dean('CCE')->create();
        $cceStudent = Student::factory()->inDepartment('CCE')->create();
        $ccjeStudent = Student::factory()->inDepartment('CCJE')->create();
        $violation = Violation::factory()->create();

        $allowedCase = StudentCase::factory()->create([
            'student_id' => $cceStudent->id,
            'violation_id' => $violation->id,
        ]);

        $blockedCase = StudentCase::factory()->create([
            'student_id' => $ccjeStudent->id,
            'violation_id' => $violation->id,
        ]);

        $this->actingAs($dean)
            ->get(route('cases.print', $allowedCase))
            ->assertOk();

        $this->actingAs($dean)
            ->get(route('cases.print', $blockedCase))
            ->assertForbidden();
    }

    public function test_dean_cannot_print_student_report_outside_department(): void
    {
        $dean = User::factory()->dean('CCE')->create();
        $cceStudent = Student::factory()->inDepartment('CCE')->create();
        $ccjeStudent = Student::factory()->inDepartment('CCJE')->create();

        $this->actingAs($dean)
            ->get(route('students.print', $cceStudent))
            ->assertOk();

        $this->actingAs($dean)
            ->get(route('students.print', $ccjeStudent))
            ->assertForbidden();
    }
}

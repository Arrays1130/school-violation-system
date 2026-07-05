<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\StudentCase;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentsIndexDebugTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_load_students_index_with_filter_params(): void
    {
        $admin = User::factory()->admin()->create();
        $violation = Violation::factory()->create(['severity' => 'Minor']);
        $student = Student::factory()->create(['academic_year' => 'SY 2024-2025']);

        StudentCase::factory()->create([
            'student_id' => $student->id,
            'violation_id' => $violation->id,
        ]);

        $this->actingAs($admin)
            ->get(route('students.index', [
                'search' => '',
                'department' => '',
                'yearLevel' => '',
                'academicYear' => 'All',
            ]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Students/Index'));
    }

    public function test_dean_can_load_students_index(): void
    {
        $dean = User::factory()->dean('CCE')->create();
        Student::factory()->inDepartment('CCE')->create(['academic_year' => 'SY 2024-2025']);

        $this->actingAs($dean)
            ->get(route('students.index'))
            ->assertOk();
    }
}

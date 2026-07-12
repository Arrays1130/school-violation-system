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

    public function test_dean_summary_only_counts_own_department_students(): void
    {
        $dean = User::factory()->dean('CCE')->create();
        Student::factory()->inDepartment('CCE')->create(['academic_year' => 'SY 2024-2025']);
        Student::factory()->inDepartment('CBAE')->create(['academic_year' => 'SY 2024-2025']);

        $this->actingAs($dean)
            ->get(route('students.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Students/Index')
                ->where('summary.total', 1)
                ->where('summary.clean', 1)
                ->has('students.data', 1)
            );
    }

    public function test_year_level_filter_matches_legacy_numeric_values(): void
    {
        $admin = User::factory()->admin()->create();
        $firstYear = Student::factory()->create([
            'year_level' => '1',
            'academic_year' => 'SY 2024-2025',
        ]);
        Student::factory()->create([
            'year_level' => '2nd Year',
            'academic_year' => 'SY 2024-2025',
        ]);

        $this->actingAs($admin)
            ->get(route('students.index', ['yearLevel' => '1st Year']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Students/Index')
                ->has('students.data', 1)
                ->where('students.data.0.id', $firstYear->id)
            );
    }
}

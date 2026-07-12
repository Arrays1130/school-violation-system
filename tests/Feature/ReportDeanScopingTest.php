<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\StudentCase;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportDeanScopingTest extends TestCase
{
    use RefreshDatabase;

    public function test_dean_reports_index_only_shows_department_cases(): void
    {
        $dean = User::factory()->dean('CCE')->create();
        $cceStudent = Student::factory()->inDepartment('CCE')->create();
        $otherStudent = Student::factory()->inDepartment('CCJE')->create();
        $violation = Violation::factory()->create();

        $cceCase = StudentCase::factory()->create([
            'student_id' => $cceStudent->id,
            'violation_id' => $violation->id,
        ]);
        StudentCase::factory()->create([
            'student_id' => $otherStudent->id,
            'violation_id' => $violation->id,
        ]);

        $this->actingAs($dean)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('cases.data', 1)
                ->where('cases.data.0.id', $cceCase->id)
            );
    }

    public function test_dean_retrieval_only_shows_department_cases(): void
    {
        $dean = User::factory()->dean('CCE')->create();
        $cceStudent = Student::factory()->inDepartment('CCE')->create();
        $otherStudent = Student::factory()->inDepartment('CCJE')->create();
        $violation = Violation::factory()->create();

        StudentCase::factory()->create([
            'student_id' => $cceStudent->id,
            'violation_id' => $violation->id,
        ]);
        StudentCase::factory()->create([
            'student_id' => $otherStudent->id,
            'violation_id' => $violation->id,
        ]);

        $this->actingAs($dean)
            ->get(route('reports.retrieval'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('cases.data', 1)
                ->where('cases.data.0.student_id', $cceStudent->id)
            );
    }
}

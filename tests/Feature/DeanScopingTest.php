<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\StudentCase;
use App\Models\User;
use App\Models\Violation;
use App\Support\DashboardCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeanScopingTest extends TestCase
{
    use RefreshDatabase;

    public function test_dean_open_cases_count_is_department_scoped(): void
    {
        $dean = User::factory()->dean('CCE')->create();
        $violation = Violation::factory()->create();

        StudentCase::factory()->create([
            'student_id' => Student::factory()->inDepartment('CCE')->create()->id,
            'violation_id' => $violation->id,
        ]);

        StudentCase::factory()->create([
            'student_id' => Student::factory()->inDepartment('CCJE')->create()->id,
            'violation_id' => $violation->id,
        ]);

        $this->actingAs($dean)
            ->get(route('cases.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('openCasesCount', 1));
    }

    public function test_dean_sanctions_report_only_includes_own_department(): void
    {
        $dean = User::factory()->dean('CCE')->create();
        $violation = Violation::factory()->create();

        $cceStudent = Student::factory()->inDepartment('CCE')->create();
        $ccjeStudent = Student::factory()->inDepartment('CCJE')->create();

        StudentCase::factory()->create([
            'student_id' => $cceStudent->id,
            'violation_id' => $violation->id,
            'sanction' => 'Warning',
        ]);

        StudentCase::factory()->create([
            'student_id' => $ccjeStudent->id,
            'violation_id' => $violation->id,
            'sanction' => 'Warning',
        ]);

        $this->actingAs($dean)
            ->get(route('reports.sanctions'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('totalSanctions', 1)
                ->has('cases.data', 1)
            );
    }
}

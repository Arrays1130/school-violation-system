<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\StudentCase;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dean_cannot_access_user_management(): void
    {
        $dean = User::factory()->dean('CCE')->create();

        $this->actingAs($dean)
            ->get(route('users.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_access_user_management(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk();
    }

    public function test_dean_cannot_create_violation_cases(): void
    {
        $dean = User::factory()->dean('CCE')->create();
        $student = Student::factory()->inDepartment('CCE')->create();
        $violation = Violation::factory()->create();

        $this->actingAs($dean)
            ->post(route('cases.store'), [
                'student_id' => $student->id,
                'violation_id' => $violation->id,
                'description' => 'Test incident',
                'occurred_at' => now()->toDateString(),
            ])
            ->assertForbidden();
    }

    public function test_admin_can_create_violation_cases(): void
    {
        $admin = User::factory()->admin()->create();
        $student = Student::factory()->inDepartment('CCE')->create();
        $violation = Violation::factory()->create();

        $this->actingAs($admin)
            ->post(route('cases.store'), [
                'student_id' => $student->id,
                'violation_id' => $violation->id,
                'description' => 'Test incident',
                'occurred_at' => now()->toDateString(),
            ])
            ->assertRedirect();
    }

    public function test_dean_only_sees_department_cases_on_index(): void
    {
        $dean = User::factory()->dean('CCE')->create();

        $cceStudent = Student::factory()->inDepartment('CCE')->create();
        $ccjeStudent = Student::factory()->inDepartment('CCJE')->create();
        $violation = Violation::factory()->create();

        StudentCase::factory()->create([
            'student_id' => $cceStudent->id,
            'violation_id' => $violation->id,
        ]);

        StudentCase::factory()->create([
            'student_id' => $ccjeStudent->id,
            'violation_id' => $violation->id,
        ]);

        $this->actingAs($dean)
            ->get(route('cases.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Cases/Index')
                ->has('violations.data', 1)
                ->where('violations.data.0.id', $violation->id)
                ->where('violations.data.0.cases_count', 1)
            );

        $this->actingAs($dean)
            ->get(route('cases.by-violation', $violation))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Cases/ByViolation')
                ->has('dayGroups.data', 1)
                ->where('dayGroups.data.0.student_count', 1)
                ->where('dayGroups.data.0.sequence_label', '000-1')
            );

        $day = now()->toDateString();

        $this->actingAs($dean)
            ->get(route('cases.by-violation.day', ['violation' => $violation, 'date' => $day]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Cases/ByViolationDay')
                ->has('cases.data', 1)
                ->where('cases.data.0.student.id', $cceStudent->id)
            )
            ->assertSee($cceStudent->full_name)
            ->assertDontSee($ccjeStudent->full_name);
    }
}

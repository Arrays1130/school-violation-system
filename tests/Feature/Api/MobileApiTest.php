<?php

namespace Tests\Feature\Api;

use App\Models\Student;
use App\Models\StudentCase;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_login_rejects_invalid_credentials(): void
    {
        $this->postJson('/api/mobile/login', [
            'email' => 'missing@example.com',
            'password' => 'wrong-password',
        ])->assertUnauthorized();
    }

    public function test_dean_cannot_view_other_department_case(): void
    {
        $dean = User::factory()->dean('CCE')->create();
        $violation = Violation::factory()->create();

        $otherDeptCase = StudentCase::factory()->create([
            'student_id' => Student::factory()->inDepartment('CCJE')->create()->id,
            'violation_id' => $violation->id,
        ]);

        Sanctum::actingAs($dean);

        $this->getJson('/api/mobile/violations/'.$otherDeptCase->id)
            ->assertForbidden();
    }

    public function test_dean_can_view_own_department_case(): void
    {
        $dean = User::factory()->dean('CCE')->create();
        $violation = Violation::factory()->create();

        $case = StudentCase::factory()->create([
            'student_id' => Student::factory()->inDepartment('CCE')->create()->id,
            'violation_id' => $violation->id,
        ]);

        Sanctum::actingAs($dean);

        $this->getJson('/api/mobile/violations/'.$case->id)
            ->assertOk()
            ->assertJsonPath('id', $case->id);
    }

    public function test_dean_can_acknowledge_endorsed_case(): void
    {
        $dean = User::factory()->dean('CCE')->create();
        $violation = Violation::factory()->create();

        $case = StudentCase::factory()->endorsed()->create([
            'student_id' => Student::factory()->inDepartment('CCE')->create()->id,
            'violation_id' => $violation->id,
        ]);

        Sanctum::actingAs($dean);

        $this->postJson('/api/mobile/cases/'.$case->id.'/acknowledge')
            ->assertOk()
            ->assertJsonPath('message', 'Case acknowledged successfully.');

        $this->assertDatabaseHas('case_actions', [
            'case_id' => $case->id,
            'user_id' => $dean->id,
        ]);
    }

    public function test_dean_can_fetch_mobile_analytics(): void
    {
        $dean = User::factory()->dean('CCE')->create();
        $violation = Violation::factory()->create();

        StudentCase::factory()->create([
            'student_id' => Student::factory()->inDepartment('CCE')->create()->id,
            'violation_id' => $violation->id,
        ]);

        Sanctum::actingAs($dean);

        $this->getJson('/api/mobile/analytics')
            ->assertOk()
            ->assertJsonStructure([
                'summary' => ['total', 'pending', 'resolved', 'major', 'minor'],
                'top_violations',
                'repeat_offenders',
                'monthly_trends',
            ])
            ->assertJsonPath('summary.total', 1);
    }
}

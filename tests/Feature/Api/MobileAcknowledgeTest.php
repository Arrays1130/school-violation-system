<?php

namespace Tests\Feature\Api;

use App\Models\Student;
use App\Models\StudentCase;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MobileAcknowledgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_dean_cannot_acknowledge_other_department_case(): void
    {
        $dean = User::factory()->dean('CCE')->create();
        $violation = Violation::factory()->create();

        $case = StudentCase::factory()->endorsed()->create([
            'student_id' => Student::factory()->inDepartment('CCJE')->create()->id,
            'violation_id' => $violation->id,
        ]);

        Sanctum::actingAs($dean);

        $this->postJson('/api/mobile/cases/'.$case->id.'/acknowledge')
            ->assertForbidden();
    }

    public function test_admin_can_acknowledge_any_department_case(): void
    {
        $admin = User::factory()->admin()->create();
        $violation = Violation::factory()->create();

        $case = StudentCase::factory()->endorsed()->create([
            'student_id' => Student::factory()->inDepartment('CCJE')->create()->id,
            'violation_id' => $violation->id,
        ]);

        Sanctum::actingAs($admin);

        $this->postJson('/api/mobile/cases/'.$case->id.'/acknowledge')
            ->assertOk();
    }
}

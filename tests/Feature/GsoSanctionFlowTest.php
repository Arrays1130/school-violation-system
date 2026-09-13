<?php

namespace Tests\Feature;

use App\Models\SanctionAssignment;
use App\Models\Student;
use App\Models\StudentCase;
use App\Models\User;
use App\Models\Violation;
use App\Services\SanctionAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GsoSanctionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_gso_can_log_dtr_and_complete_to_osa(): void
    {
        $admin = User::factory()->admin()->create();
        $gso = User::factory()->gso()->create();
        $student = Student::factory()->create();
        $violation = Violation::factory()->create(['severity' => 'Minor']);

        $case = StudentCase::createForStaff([
            'student_id' => $student->id,
            'violation_id' => $violation->id,
            'description' => 'Test case',
            'occurred_at' => now()->subDay(),
            'offense_level' => 1,
            'sanction' => '4 hours community service',
        ], $admin->id);

        $service = app(SanctionAssignmentService::class);
        $assignment = $service->assignHours($case, 0.25, $admin);

        Sanctum::actingAs($gso);

        $this->postJson("/api/mobile/gso/sanctions/{$assignment->id}/time-in")
            ->assertOk();

        // Force enough elapsed time for hours calculation
        $open = $assignment->fresh()->openDtrEntry();
        $open->update(['time_in' => now()->subMinutes(20)]);

        $this->postJson("/api/mobile/gso/sanctions/{$assignment->id}/time-out")
            ->assertOk();

        $this->postJson("/api/mobile/gso/sanctions/{$assignment->id}/complete")
            ->assertOk()
            ->assertJsonPath('assignment.status', SanctionAssignment::STATUS_SUBMITTED_TO_OSA);

        $this->assertFalse($case->fresh()->hasPendingGsoSanction());
        $this->assertTrue($case->fresh()->canClose());
    }

    public function test_case_cannot_close_while_gso_pending(): void
    {
        $admin = User::factory()->admin()->create();
        $student = Student::factory()->create();
        $violation = Violation::factory()->create(['severity' => 'Minor']);

        $case = StudentCase::createForStaff([
            'student_id' => $student->id,
            'violation_id' => $violation->id,
            'description' => 'Test case',
            'occurred_at' => now()->subDay(),
            'offense_level' => 1,
            'sanction' => '8 hours community service',
        ], $admin->id);

        app(SanctionAssignmentService::class)->assignHours($case, 8, $admin);

        $this->assertNotNull($case->fresh()->closureBlockReason());
        $this->assertFalse($case->fresh()->canClose());
    }
}

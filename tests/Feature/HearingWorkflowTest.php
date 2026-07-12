<?php

namespace Tests\Feature;

use App\Models\Hearing;
use App\Models\StudentCase;
use App\Models\User;
use App\Models\Violation;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HearingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_hearing_start_and_complete_updates_case_status(): void
    {
        $admin = User::factory()->admin()->create();
        $student = Student::factory()->create();
        $violation = Violation::factory()->create();
        $case = StudentCase::factory()->create([
            'student_id' => $student->id,
            'violation_id' => $violation->id,
            'status' => 'Hearing Scheduled',
        ]);

        $hearing = Hearing::create([
            'case_id' => $case->id,
            'venue' => 'OSA Office',
            'scheduled_at' => now()->addDay(),
            'participants' => ['Student', 'Dean'],
        ]);

        $this->actingAs($admin)
            ->post(route('hearings.start', $hearing))
            ->assertRedirect();

        $case->refresh();
        $this->assertSame('Hearing', $case->status);

        $this->actingAs($admin)
            ->post(route('hearings.complete', $hearing), [
                'sanction' => 'Written apology and community service',
            ])
            ->assertRedirect();

        $case->refresh();
        $this->assertSame('Closed', $case->status);
        $this->assertSame('Written apology and community service', $case->sanction);
    }
}

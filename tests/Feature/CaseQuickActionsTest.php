<?php

namespace Tests\Feature;

use App\Models\StudentCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaseQuickActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_endorse_close_and_soft_delete_pending_case(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $case = StudentCase::factory()->create();

        $this->actingAs($admin)
            ->post(route('cases.endorse', $case))
            ->assertRedirect()
            ->assertSessionHas('success');

        $case->refresh();
        $this->assertNotNull($case->endorsed_at);

        $this->actingAs($admin)
            ->post(route('cases.close', $case))
            ->assertRedirect()
            ->assertSessionHas('success');

        $case->refresh();
        $this->assertSame('Closed', $case->status);

        $case2 = StudentCase::factory()->create();
        $this->actingAs($admin)
            ->from(route('cases.show', $case2))
            ->delete(route('cases.destroy', $case2))
            ->assertRedirect(route('cases.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('cases', ['id' => $case2->id]);
    }

    public function test_staff_can_open_schedule_hearing_page(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $case = StudentCase::factory()->create();

        $this->actingAs($admin)
            ->get(route('hearings.create', $case))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Hearings/Create'));
    }

    public function test_major_offense_is_auto_endorsed_on_create(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $student = \App\Models\Student::factory()->create();
        $violation = \App\Models\Violation::factory()->major()->create();

        $this->actingAs($admin)
            ->post(route('cases.store'), [
                'student_id' => $student->id,
                'violation_id' => $violation->id,
                'description' => 'Single major offense',
                'occurred_at' => now()->toDateTimeString(),
            ])
            ->assertRedirect();

        $case = StudentCase::query()
            ->where('student_id', $student->id)
            ->where('violation_id', $violation->id)
            ->first();

        $this->assertNotNull($case);
        $this->assertNotNull($case->endorsed_at);
        $this->assertTrue(
            $case->actions()->where('endorsed_to_grievance', true)->exists()
        );
    }

    public function test_sanction_info_uses_recurring_rule_and_auto_endorse_flag(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $student = \App\Models\Student::factory()->create();
        $violation = \App\Models\Violation::factory()->create([
            'first_offense' => 'Verbal warning',
            'second_offense' => 'Written warning',
            'third_offense' => 'Parent conference',
        ]);

        StudentCase::factory()->count(3)->create([
            'student_id' => $student->id,
            'violation_id' => $violation->id,
        ]);

        $this->actingAs($admin)
            ->get(route('api.get-sanction-info', [
                'student_id' => $student->id,
                'violation_id' => $violation->id,
            ]))
            ->assertOk()
            ->assertJsonPath('offense_level', 4)
            ->assertJsonPath('sanction', \App\Services\OffenseAdviceService::RECURRING_SANCTION)
            ->assertJsonPath('auto_endorse', false);

        $major = \App\Models\Violation::factory()->major()->create();
        $this->actingAs($admin)
            ->get(route('api.get-sanction-info', [
                'student_id' => $student->id,
                'violation_id' => $major->id,
            ]))
            ->assertOk()
            ->assertJsonPath('auto_endorse', true);
    }
}

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

    public function test_major_offense_endorse_blocked_without_osa_action(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $case = StudentCase::factory()->create([
            'violation_id' => \App\Models\Violation::factory()->major(),
        ]);

        $this->actingAs($admin)
            ->post(route('cases.endorse', $case))
            ->assertRedirect()
            ->assertSessionHas('error');

        $case->refresh();
        $this->assertNull($case->endorsed_at);
    }
}

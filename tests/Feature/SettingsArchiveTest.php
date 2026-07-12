<?php

namespace Tests\Feature;

use App\Models\StudentCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsArchiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_archive_closed_cases(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $closed = StudentCase::factory()->closed()->create(['is_archived' => false]);
        $pending = StudentCase::factory()->create(['status' => 'Pending', 'is_archived' => false]);

        $this->actingAs($admin)
            ->post(route('settings.archive-cases'))
            ->assertRedirect();

        $this->assertTrue($closed->fresh()->is_archived);
        $this->assertFalse($pending->fresh()->is_archived);
    }

    public function test_admin_cannot_archive_closed_cases(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('settings.archive-cases'))
            ->assertForbidden();
    }

    public function test_dean_cannot_archive_closed_cases(): void
    {
        $dean = User::factory()->dean('CCE')->create();

        $this->actingAs($dean)
            ->post(route('settings.archive-cases'))
            ->assertForbidden();
    }
}

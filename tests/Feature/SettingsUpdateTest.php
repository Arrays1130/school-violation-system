<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_update_settings(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->post(route('settings.update'), [
                'school_name' => 'Updated School',
                'current_academic_year' => 'SY 2026-2027',
            ])
            ->assertRedirect();
    }

    public function test_dean_cannot_update_settings(): void
    {
        $dean = User::factory()->dean('CCE')->create();

        $this->actingAs($dean)
            ->post(route('settings.update'), [
                'school_name' => 'Hacked School',
                'current_academic_year' => 'SY 2099-2100',
            ])
            ->assertForbidden();
    }
}

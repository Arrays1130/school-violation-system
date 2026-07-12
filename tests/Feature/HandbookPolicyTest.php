<?php

namespace Tests\Feature;

use App\Models\Handbook;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HandbookPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_dean_can_view_handbooks(): void
    {
        $dean = User::factory()->dean('CCE')->create();
        $handbook = Handbook::create([
            'title' => 'Student Handbook 2025',
            'content' => 'Policy content',
        ]);

        $this->actingAs($dean)
            ->get(route('handbooks.show', $handbook))
            ->assertOk();
    }

    public function test_dean_cannot_create_handbook(): void
    {
        $dean = User::factory()->dean('CCE')->create();

        $this->actingAs($dean)
            ->get(route('handbooks.create'))
            ->assertForbidden();
    }

    public function test_admin_can_create_handbook(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('handbooks.create'))
            ->assertOk();
    }
}

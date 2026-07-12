<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HearingsCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_hearings_calendar(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('hearings.calendar'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Hearings/Calendar'));
    }
}

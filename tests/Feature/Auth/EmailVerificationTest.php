<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_staff_are_not_blocked_by_email_verification(): void
    {
        $user = User::factory()->unverified()->admin()->create();

        $this->actingAs($user)
            ->get(route('students.index'))
            ->assertOk();
    }

    public function test_email_verification_notice_still_renders(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get('/verify-email')
            ->assertOk();
    }
}

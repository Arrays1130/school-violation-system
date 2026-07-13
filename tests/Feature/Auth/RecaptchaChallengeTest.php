<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecaptchaChallengeTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_is_redirected_when_recaptcha_is_disabled(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->get(route('recaptcha.challenge'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_guest_cannot_access_recaptcha_challenge_route(): void
    {
        $this->get(route('recaptcha.challenge'))
            ->assertRedirect(route('login'));
    }
}

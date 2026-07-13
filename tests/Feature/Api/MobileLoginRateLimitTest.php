<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class MobileLoginRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_login_is_rate_limited_per_email_and_ip(): void
    {
        $user = User::factory()->admin()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/mobile/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])->assertUnauthorized();
        }

        $this->postJson('/api/mobile/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertStatus(429);

        RateLimiter::clear(strtolower($user->email).'|127.0.0.1');
    }
}

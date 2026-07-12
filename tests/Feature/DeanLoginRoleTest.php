<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeanLoginRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_dean_account_cannot_use_dean_login(): void
    {
        $user = User::factory()->create([
            'email' => 'staff@ilinkcst.edu.ph',
            'password' => bcrypt('password'),
            'role' => 'staff',
        ]);

        $response = $this->post(route('dean.login.post'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_dean_can_use_dean_login(): void
    {
        $dean = User::factory()->dean('CCE')->create([
            'email' => 'dean@ilinkcst.edu.ph',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(route('dean.login.post'), [
            'email' => $dean->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dean.dashboard'));
        $this->assertAuthenticatedAs($dean);
    }
}

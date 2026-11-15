<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_login_and_logout(): void
    {
        $user = User::factory()->create([
            'username' => 'admin-test',
            'password' => 'password-rahasia',
            'status' => 'aktif',
        ]);

        $this->post(route('login.store'), [
            'username' => 'admin-test',
            'password' => 'password-rahasia',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->create([
            'username' => 'nonaktif',
            'password' => 'password-rahasia',
            'status' => 'non_aktif',
        ]);

        $this->from(route('login'))->post(route('login.store'), [
            'username' => 'nonaktif',
            'password' => 'password-rahasia',
        ])->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_login_shows_remaining_lockout_time_after_five_failed_attempts(): void
    {
        $this->freezeTime();

        $credentials = ['username' => 'locked-user', 'password' => 'wrong-password'];

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->from(route('login'))
                ->post(route('login.store'), $credentials)
                ->assertRedirect(route('login'));
        }

        $this->travel(10)->seconds();

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('<span id="login-retry-seconds">50</span>', false);

        $this->assertGuest();
    }
}

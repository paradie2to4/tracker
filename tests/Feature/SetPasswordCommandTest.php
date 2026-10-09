<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SetPasswordCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_password_of_an_existing_user_can_be_set_and_they_can_log_in(): void
    {
        $user = User::factory()->create(['email' => 'owner@example.com']);

        $this->artisan('app:set-password', ['email' => 'Owner@Example.com', '--admin' => true])
            ->expectsQuestion('New password', 'a-new-password-2026')
            ->expectsQuestion('Confirm new password', 'a-new-password-2026')
            ->expectsOutputToContain('Password updated for owner@example.com (Administrator)')
            ->assertSuccessful();

        $user->refresh();
        $this->assertTrue(Hash::check('a-new-password-2026', $user->password));
        $this->assertSame(UserRole::Admin, $user->role);

        $this->post('/login', ['email' => 'owner@example.com', 'password' => 'a-new-password-2026'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_an_unknown_email_is_reported(): void
    {
        $this->artisan('app:set-password', ['email' => 'nobody@example.com'])
            ->expectsOutputToContain('No account exists for nobody@example.com')
            ->assertFailed();
    }

    public function test_weak_passwords_are_rejected(): void
    {
        $user = User::factory()->create();
        $original = $user->password;

        $this->artisan('app:set-password', ['email' => $user->email])
            ->expectsQuestion('New password', 'short')
            ->expectsQuestion('Confirm new password', 'short')
            ->assertFailed();

        $this->assertSame($original, $user->fresh()->password);
    }

    public function test_the_login_page_has_a_show_password_toggle(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('data-password-toggle="password"', false)
            ->assertSee('aria-label="Show password"', false);
    }
}

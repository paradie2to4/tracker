<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function validInput(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'secret-password-123',
            'password_confirmation' => 'secret-password-123',
        ], $overrides);
    }

    public function test_the_registration_page_can_be_rendered(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Create your account');
    }

    public function test_the_login_page_links_to_registration(): void
    {
        $this->get('/login')->assertSee(route('register'));
    }

    public function test_new_users_can_register_and_are_logged_in(): void
    {
        $this->post('/register', $this->validInput())
            ->assertRedirect('/dashboard');

        $user = User::where('email', 'jane@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame('Jane Doe', $user->name);
        $this->assertTrue(password_verify('secret-password-123', $user->getAttributes()['password']));
    }

    public function test_registered_users_get_the_staff_role_even_if_they_ask_for_admin(): void
    {
        $this->post('/register', $this->validInput(['role' => 'admin']));

        $this->assertSame(UserRole::Staff, User::where('email', 'jane@example.com')->firstOrFail()->role);
    }

    public function test_emails_are_stored_in_lowercase(): void
    {
        $this->post('/register', $this->validInput(['email' => '  Jane@Example.COM ']));

        $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
    }

    public function test_an_email_cannot_be_registered_twice(): void
    {
        User::factory()->create(['email' => 'jane@example.com']);

        $this->from('/register')
            ->post('/register', $this->validInput(['email' => 'JANE@example.com']))
            ->assertRedirect('/register')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertSame(1, User::count());
    }

    public function test_weak_or_mismatched_passwords_are_rejected(): void
    {
        $this->post('/register', $this->validInput(['password' => 'short1', 'password_confirmation' => 'short1']))
            ->assertSessionHasErrors('password');

        $this->post('/register', $this->validInput(['password_confirmation' => 'something-else-123']))
            ->assertSessionHasErrors('password');

        $this->assertGuest();
        $this->assertSame(0, User::count());
    }

    public function test_logged_in_users_cannot_see_the_registration_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/register')
            ->assertRedirect('/dashboard');
    }
}

<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoAccountsTest extends TestCase
{
    use RefreshDatabase;

    private function configureDemoPasswords(?string $admin, ?string $staff): void
    {
        config([
            'productsphere.demo_accounts.admin.password' => $admin,
            'productsphere.demo_accounts.staff.password' => $staff,
        ]);
    }

    public function test_configured_demo_accounts_can_sign_in_with_their_roles(): void
    {
        $this->configureDemoPasswords('admin-demo-pass-1', 'staff-demo-pass-1');

        $this->artisan('app:seed-demo')->assertSuccessful();

        $admin = User::where('email', 'admin@productsphere.demo')->sole();
        $staff = User::where('email', 'staff@productsphere.demo')->sole();
        $this->assertSame(UserRole::Admin, $admin->role);
        $this->assertSame(UserRole::Staff, $staff->role);

        // The demo history is attributed to the demo accounts.
        $this->assertTrue(Shipment::where('dispatched_by', $staff->id)->exists());

        $this->post('/login', ['email' => 'admin@productsphere.demo', 'password' => 'admin-demo-pass-1'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_changing_the_configured_password_updates_the_account_on_the_next_run(): void
    {
        $this->configureDemoPasswords('first-password-1', null);
        $this->artisan('app:seed-demo');

        $this->configureDemoPasswords('second-password-2', null);
        $this->artisan('app:seed-demo')->expectsOutputToContain('already present')->assertSuccessful();

        $admin = User::where('email', 'admin@productsphere.demo')->sole();
        $this->assertTrue(Hash::check('second-password-2', $admin->password));
        $this->assertFalse(Hash::check('first-password-1', $admin->password));
    }

    public function test_without_configured_passwords_nobody_can_sign_in_as_a_demo_account(): void
    {
        $this->configureDemoPasswords(null, null);

        $this->artisan('app:seed-demo')->expectsOutputToContain('sign-in disabled');

        $this->post('/login', ['email' => 'admin@productsphere.demo', 'password' => 'password']);
        $this->assertGuest();
    }

    public function test_the_login_page_lists_demo_emails_only_when_sign_in_is_enabled(): void
    {
        $this->configureDemoPasswords(null, null);
        $this->get('/login')->assertDontSee('admin@productsphere.demo');

        $this->configureDemoPasswords('admin-demo-pass-1', null);
        $this->get('/login')
            ->assertSee('admin@productsphere.demo')
            ->assertDontSee('admin-demo-pass-1');
    }
}

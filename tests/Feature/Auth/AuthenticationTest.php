<?php

namespace Tests\Feature\Auth;

use App\Models\Batch;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string}>
     */
    public static function protectedPages(): array
    {
        return [
            'dashboard' => ['/dashboard'],
            'product list' => ['/products'],
            'create product' => ['/products/create'],
            'batch list' => ['/batches'],
            'create batch' => ['/batches/create'],
            'shipment list' => ['/shipments'],
            'create shipment' => ['/shipments/create'],
            'supply chain' => ['/organizations'],
            'audit log' => ['/audit-log'],
        ];
    }

    #[DataProvider('protectedPages')]
    public function test_guests_are_redirected_to_the_login_page(string $uri): void
    {
        $this->get($uri)->assertRedirect('/login');
    }

    public function test_guests_cannot_view_or_modify_records(): void
    {
        $batch = Batch::factory()->create();

        $this->get(route('products.show', $batch->product))->assertRedirect('/login');
        $this->get(route('batches.show', $batch))->assertRedirect('/login');
        $this->post(route('products.store'), [])->assertRedirect('/login');
        $this->put(route('batches.update', $batch), [])->assertRedirect('/login');
        $this->post(route('batches.recall', $batch), ['recall_reason' => 'Attempted by a guest.'])->assertRedirect('/login');

        $this->assertNull($batch->fresh()->recalled_at);
        $this->assertSame(1, Product::count());
    }

    public function test_signed_in_users_skip_the_welcome_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertRedirect('/dashboard');
    }

    public function test_the_login_page_can_be_rendered(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Log in to your account');
    }

    public function test_users_can_log_in_with_valid_credentials(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    }

    public function test_users_cannot_log_in_with_an_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->from('/login')
            ->post('/login', ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_attempts_are_rate_limited(): void
    {
        $user = User::factory()->create();

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password']);
        }

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertTooManyRequests();

        $this->assertGuest();
    }

    public function test_passwords_are_stored_hashed(): void
    {
        $user = User::factory()->create(['password' => 'a-plain-password-123']);

        $this->assertNotSame('a-plain-password-123', $user->getAttributes()['password']);
        $this->assertTrue(password_verify('a-plain-password-123', $user->getAttributes()['password']));
    }

    public function test_users_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/');

        $this->assertGuest();
    }
}

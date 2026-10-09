<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateUserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_administrator_account_can_be_created(): void
    {
        $this->artisan('app:create-user', ['--admin' => true])
            ->expectsQuestion('Full name', 'Aline Uwase')
            ->expectsQuestion('Email address', 'Aline@Example.com')
            ->expectsQuestion('Password', 'secure-pass-2026')
            ->expectsQuestion('Confirm password', 'secure-pass-2026')
            ->expectsOutputToContain('Created Administrator account for aline@example.com.')
            ->assertSuccessful();

        $user = User::sole();
        $this->assertSame('aline@example.com', $user->email);
        $this->assertSame(UserRole::Admin, $user->role);
        $this->assertTrue(Hash::check('secure-pass-2026', $user->password));
    }

    public function test_weak_or_mismatched_passwords_are_rejected(): void
    {
        $this->artisan('app:create-user', ['--admin' => true])
            ->expectsQuestion('Full name', 'Aline Uwase')
            ->expectsQuestion('Email address', 'aline@example.com')
            ->expectsQuestion('Password', 'short')
            ->expectsQuestion('Confirm password', 'different')
            ->assertFailed();

        $this->assertSame(0, User::count());
    }
}

<?php

namespace App\Actions\Demo;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Creates (or updates) one demo sign-in account per role from
 * config('productsphere.demo_accounts').
 *
 * - A configured password is applied, so changing DEMO_*_PASSWORD on the
 *   host and redeploying changes the password.
 * - Without a configured password, a new account gets a random one that
 *   nobody knows, and an existing account keeps its current password.
 */
final class SyncDemoAccounts
{
    /**
     * @return array{admin: User, staff: User}
     */
    public function handle(): array
    {
        return [
            'admin' => $this->sync('admin', UserRole::Admin),
            'staff' => $this->sync('staff', UserRole::Staff),
        ];
    }

    /**
     * Whether at least one demo account can be signed in to.
     */
    public static function anyEnabled(): bool
    {
        return collect(config('productsphere.demo_accounts'))
            ->contains(fn (array $account): bool => filled($account['password']));
    }

    private function sync(string $key, UserRole $role): User
    {
        /** @var array{name: string, email: string, password: string|null} $account */
        $account = config("productsphere.demo_accounts.{$key}");

        $user = User::firstOrNew(['email' => Str::lower($account['email'])]);
        $user->name = $account['name'];
        $user->role = $role;
        $user->email_verified_at ??= now();

        if (filled($account['password'])) {
            if (! $user->exists || ! Hash::check($account['password'], $user->password)) {
                $user->password = $account['password'];
            }
        } elseif (! $user->exists) {
            $user->password = Str::password(40);
        }

        $user->save();

        return $user;
    }
}

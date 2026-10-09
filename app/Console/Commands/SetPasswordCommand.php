<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;

/**
 * Sets a new password for an existing account (and optionally makes it an
 * administrator). Useful when a password is forgotten, or to promote a
 * self-registered account. The password is typed at a hidden prompt.
 */
class SetPasswordCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'app:set-password {email : Email address of the account} {--admin : Also give the account the administrator role}';

    /**
     * @var string
     */
    protected $description = 'Set a new password for an existing user (optionally promoting them to administrator)';

    public function handle(): int
    {
        $email = Str::lower(trim((string) $this->argument('email')));
        $user = User::where('email', $email)->first();

        if ($user === null) {
            $this->error("No account exists for {$email}. Create it with: php artisan app:create-user --admin");

            return self::FAILURE;
        }

        $password = password(label: 'New password', required: true, hint: 'At least 10 characters, including letters and numbers.');
        $confirmation = password(label: 'Confirm new password', required: true);

        $validator = Validator::make(
            ['password' => $password, 'password_confirmation' => $confirmation],
            ['password' => ['required', 'confirmed', Password::defaults()]],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user->password = $password;

        if ($this->option('admin')) {
            $user->role = UserRole::Admin;
        }

        $user->save();

        $this->info("Password updated for {$email} ({$user->role->label()}).");

        return self::SUCCESS;
    }
}

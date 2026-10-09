<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

/**
 * Public sign-up only creates Staff accounts, so administrators are created here.
 * The password is typed into a hidden prompt and never appears in shell
 * history or command-line arguments.
 */
class CreateUserCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'app:create-user {--admin : Give the new user the administrator role}';

    /**
     * @var string
     */
    protected $description = 'Create a ProductSphere user account';

    public function handle(): int
    {
        $name = text(label: 'Full name', required: true);
        $email = Str::lower(trim(text(label: 'Email address', required: true)));

        $role = $this->option('admin')
            ? UserRole::Admin
            : UserRole::from(select(
                label: 'Role',
                options: collect(UserRole::cases())->mapWithKeys(fn (UserRole $role) => [$role->value => $role->label()])->all(),
                default: UserRole::Staff->value,
            ));

        $password = password(label: 'Password', required: true, hint: 'At least 10 characters, including letters and numbers.');
        $confirmation = password(label: 'Confirm password', required: true);

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password, 'password_confirmation' => $confirmation],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
                'password' => ['required', 'confirmed', Password::defaults()],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = new User(['name' => $name, 'email' => $email, 'password' => $password]);
        $user->role = $role;
        $user->email_verified_at = now();
        $user->save();

        $this->info("Created {$role->label()} account for {$email}.");

        return self::SUCCESS;
    }
}

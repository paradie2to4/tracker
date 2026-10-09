<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Lists accounts (never passwords) in the database the app is connected
 * to. Handy for checking which database a command is really talking to.
 */
class ListUsersCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'app:list-users';

    /**
     * @var string
     */
    protected $description = 'List user accounts with their roles';

    public function handle(): int
    {
        $this->line('Database: '.config('database.default').' / '.(config('database.connections.'.config('database.default').'.host') ?? 'n/a'));

        $users = User::orderBy('created_at')->get(['name', 'email', 'role', 'created_at']);

        if ($users->isEmpty()) {
            $this->warn('No user accounts exist in this database.');

            return self::SUCCESS;
        }

        $this->table(
            ['Name', 'Email', 'Role', 'Created'],
            $users->map(fn (User $user) => [$user->name, $user->email, $user->role->label(), $user->created_at?->format('Y-m-d H:i')]),
        );

        return self::SUCCESS;
    }
}

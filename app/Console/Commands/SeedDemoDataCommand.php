<?php

namespace App\Console\Commands;

use App\Actions\Demo\SyncDemoAccounts;
use App\Models\Organization;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Console\Command;

/**
 * Loads the demo supply chain once and keeps the demo sign-in accounts in
 * sync with DEMO_ADMIN_PASSWORD / DEMO_STAFF_PASSWORD. Safe to run on every
 * deploy (see docker/start.sh, enabled with SEED_DEMO_DATA=true).
 */
class SeedDemoDataCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'app:seed-demo';

    /**
     * @var string
     */
    protected $description = 'Load the demo supply chain (once) and sync the demo sign-in accounts';

    public function handle(SyncDemoAccounts $syncDemoAccounts): int
    {
        if (Organization::where('name', DemoDataSeeder::MARKER_ORGANIZATION)->exists()) {
            $this->info('Demo data is already present.');
        } else {
            $this->call('db:seed', ['--class' => DemoDataSeeder::class, '--force' => true]);
            $this->info('Demo supply chain loaded.');
        }

        $accounts = $syncDemoAccounts->handle();

        foreach ($accounts as $key => $user) {
            $status = filled(config("productsphere.demo_accounts.{$key}.password")) ? 'sign-in enabled' : 'no password set, sign-in disabled';
            $this->line("Demo {$user->role->label()}: {$user->email} ({$status})");
        }

        return self::SUCCESS;
    }
}

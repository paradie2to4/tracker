<?php

namespace App\Console\Commands;

use App\Models\Organization;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Console\Command;

/**
 * Loads the demo supply chain once. Safe to run on every deploy: it does
 * nothing if the demo data is already present. Enabled on Render through
 * SEED_DEMO_DATA=true (see docker/start.sh).
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
    protected $description = 'Load the demo supply chain (once) so visitors have data to explore';

    public function handle(): int
    {
        if (Organization::where('name', DemoDataSeeder::MARKER_ORGANIZATION)->exists()) {
            $this->info('Demo data is already present. Nothing to do.');

            return self::SUCCESS;
        }

        $this->call('db:seed', ['--class' => DemoDataSeeder::class, '--force' => true]);

        $this->info('Demo supply chain loaded.');

        return self::SUCCESS;
    }
}

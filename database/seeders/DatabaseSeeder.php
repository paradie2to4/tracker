<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Local development data: two sign-in accounts with the password "password"
 * plus the demo supply chain. Refuses to run outside APP_ENV=local because
 * of those known passwords. For production, use `php artisan app:seed-demo`,
 * which loads the same supply chain without any known-password account.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command->warn('Local accounts are only seeded when APP_ENV=local. Use `php artisan app:seed-demo` for demo data.');

            return;
        }

        User::factory()->admin()->create([
            'name' => 'Local Administrator',
            'email' => 'admin@productsphere.test',
        ]);

        User::factory()->create([
            'name' => 'Local Staff',
            'email' => 'staff@productsphere.test',
        ]);

        $this->call(DemoDataSeeder::class);
    }
}

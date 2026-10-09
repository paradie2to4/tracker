<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with demo data.
     *
     * The demo accounts use the factory password "password", so this seeder
     * refuses to run outside the local environment.
     */
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command->warn('Demo data is only seeded when APP_ENV=local. Nothing was seeded.');

            return;
        }

        User::factory()->admin()->create([
            'name' => 'Demo Administrator',
            'email' => 'admin@productsphere.test',
        ]);

        User::factory()->create([
            'name' => 'Demo Staff',
            'email' => 'staff@productsphere.test',
        ]);

        Product::factory(12)
            ->has(Batch::factory()->count(3))
            ->create();

        $products = Product::all();

        Batch::factory(3)->expired()->recycle($products)->create();
        Batch::factory(2)->expiringSoon(5)->recycle($products)->create();
        Batch::factory(2)->expiringSoon(21)->recycle($products)->create();
        Batch::factory(2)->depleted()->recycle($products)->create();
        Batch::factory()->recalled()->recycle($products)->create();
        Batch::factory(2)->withoutExpiry()->recycle($products)->create();

        Product::factory(2)->inactive()->create();
    }
}

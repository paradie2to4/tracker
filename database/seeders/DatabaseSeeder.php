<?php

namespace Database\Seeders;

use App\Actions\Shipments\CancelShipment;
use App\Actions\Shipments\DispatchShipment;
use App\Actions\Shipments\ReceiveShipment;
use App\Actions\Stock\RecordStockRemoval;
use App\Enums\BatchStatus;
use App\Enums\LocationType;
use App\Enums\OrganizationType;
use App\Enums\RemovalReason;
use App\Models\Batch;
use App\Models\Location;
use App\Models\Organization;
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

        $admin = User::factory()->admin()->create([
            'name' => 'Demo Administrator',
            'email' => 'admin@productsphere.test',
        ]);

        $staff = User::factory()->create([
            'name' => 'Demo Staff',
            'email' => 'staff@productsphere.test',
        ]);

        // --- Supply chain -------------------------------------------------
        $factory = $this->location('Kigali Mills Ltd', OrganizationType::Manufacturer, 'KGL-PLANT-01', 'Kigali Mills Masoro plant', LocationType::Factory, 'Gasabo');
        $pharmaPlant = $this->location('Rwanda Health Products Ltd', OrganizationType::Manufacturer, 'KCK-PLANT-01', 'Kicukiro manufacturing site', LocationType::Factory, 'Kicukiro');
        $warehouse = $this->location('Inyange Distribution Co.', OrganizationType::Distributor, 'KGL-WH-01', 'Kigali central warehouse', LocationType::Warehouse, 'Nyarugenge');
        $huyeDepot = $this->location('Southern Wholesalers', OrganizationType::Wholesaler, 'HUY-DC-01', 'Huye distribution centre', LocationType::DistributionCentre, 'Huye');
        $musanzeShop = $this->location('Musanze Fresh Market', OrganizationType::Retailer, 'MUS-SHOP-01', 'Musanze town shop', LocationType::Store, 'Musanze');
        $rubavuShop = $this->location('Lake Kivu Pharmacy', OrganizationType::Retailer, 'RUB-SHOP-01', 'Rubavu pharmacy', LocationType::Store, 'Rubavu');

        $origins = collect([$factory, $pharmaPlant]);

        // --- Products and batches -----------------------------------------
        Product::factory(12)->create();
        $products = Product::all();

        Batch::factory(24)->recycle($products)->recycle($origins)->create();
        Batch::factory(3)->expired()->recycle($products)->recycle($origins)->create();
        Batch::factory(2)->expiringSoon(5)->recycle($products)->recycle($origins)->create();
        Batch::factory(2)->expiringSoon(21)->recycle($products)->recycle($origins)->create();
        Batch::factory(2)->depleted()->recycle($products)->recycle($origins)->create();
        Batch::factory()->recalled()->recycle($products)->recycle($origins)->create();
        Batch::factory(2)->withoutExpiry()->recycle($products)->recycle($origins)->create();

        Product::factory(2)->inactive()->create();

        // --- Movements, through the real actions --------------------------
        $dispatch = app(DispatchShipment::class);
        $receive = app(ReceiveShipment::class);

        $shippable = fn (Location $at) => Batch::query()
            ->withStatus(BatchStatus::Active)
            ->whereHas('stockBalances', fn ($query) => $query->where('location_id', $at->id)->where('quantity', '>=', 100))
            ->orderBy('id');

        // Factory -> warehouse, received; then warehouse -> shops.
        foreach ($shippable($factory)->limit(4)->get() as $batch) {
            $shipment = $dispatch->handle($factory, $warehouse, [$batch->id => '40'], 'Truck RAD 123 A', $staff);
            $receive->handle($shipment, $staff);

            $onward = $dispatch->handle($warehouse, $batch->id % 2 ? $musanzeShop : $huyeDepot, [$batch->id => '15'], null, $staff);
            $receive->handle($onward, $staff);

            app(RecordStockRemoval::class)->handle($batch, $onward->toLocation, '5', RemovalReason::Sold, null, $staff);
        }

        // Pharmaceuticals plant -> pharmacy: one received, one in transit.
        $pharmaBatches = $shippable($pharmaPlant)->limit(2)->get();

        if ($pharmaBatches->count() === 2) {
            $receive->handle($dispatch->handle($pharmaPlant, $rubavuShop, [$pharmaBatches[0]->id => '25'], null, $admin), $staff);
            $dispatch->handle($pharmaPlant, $rubavuShop, [$pharmaBatches[1]->id => '30'], 'Cold chain van', $admin);
        }

        // A cancelled shipment.
        if ($batch = $shippable($factory)->first()) {
            $mistake = $dispatch->handle($factory, $huyeDepot, [$batch->id => '10'], null, $staff);
            app(CancelShipment::class)->handle($mistake, 'Wrong destination entered by mistake.', $admin);
        }
    }

    private function location(string $organization, OrganizationType $type, string $code, string $name, LocationType $locationType, string $district): Location
    {
        return Organization::factory()
            ->create(['name' => $organization, 'type' => $type])
            ->locations()
            ->create(['code' => $code, 'name' => $name, 'type' => $locationType, 'district' => $district]);
    }
}

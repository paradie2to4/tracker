<?php

namespace Database\Seeders;

use App\Actions\Batches\RecallBatch;
use App\Actions\Batches\RegisterBatch;
use App\Actions\Shipments\CancelShipment;
use App\Actions\Shipments\DispatchShipment;
use App\Actions\Shipments\ReceiveShipment;
use App\Actions\Stock\RecordStockRemoval;
use App\Enums\LocationType;
use App\Enums\OrganizationType;
use App\Enums\ProductCategory;
use App\Enums\RemovalReason;
use App\Enums\UnitOfMeasure;
use App\Enums\UserRole;
use App\Models\Batch;
use App\Models\Location;
use App\Models\Organization;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * A realistic, fictional Rwandan supply chain for demonstrations.
 *
 * Safe for production:
 * - No Faker (a dev-only dependency), so it runs with `composer install --no-dev`.
 * - No account with a known password. The two demo actors get random
 *   passwords nobody knows; visitors explore by signing up as Staff.
 * - Every stock change goes through the real action classes, so the ledger,
 *   balances and audit trail are exactly what the application would produce.
 * - The clock is moved back for each step, so the history spans four months
 *   instead of being stamped with the seeding minute.
 *
 * Run it with `php artisan app:seed-demo`, which skips if it has already run.
 */
class DemoDataSeeder extends Seeder
{
    /**
     * Organisation whose presence means the demo data has already been seeded.
     */
    public const MARKER_ORGANIZATION = 'Umurage Mills Ltd';

    private Carbon $today;

    private User $operations;

    private User $quality;

    /** @var array<string, Location> */
    private array $locations = [];

    /** @var array<string, Product> */
    private array $products = [];

    /** @var array<string, Batch> */
    private array $batches = [];

    public function run(): void
    {
        $this->today = Carbon::today();

        try {
            DB::transaction(function () {
                $this->at(130);
                $this->createActors();
                $this->createSupplyChain();
                $this->createProducts();
                $this->registerBatches();
                $this->moveStock();
            });
        } finally {
            Carbon::setTestNow();
        }
    }

    /**
     * Move the application clock to N days before today (at a working hour).
     */
    private function at(int $daysAgo, int $hour = 9, int $minute = 30): void
    {
        Carbon::setTestNow($this->today->copy()->subDays($daysAgo)->setTime($hour, $minute));
    }

    private function createActors(): void
    {
        $this->operations = $this->actor('Demo Operations Team', 'demo-operations@productsphere.example', UserRole::Staff);
        $this->quality = $this->actor('Demo Quality Lead', 'demo-quality@productsphere.example', UserRole::Admin);
    }

    private function actor(string $name, string $email, UserRole $role): User
    {
        $user = new User([
            'name' => $name,
            'email' => $email,
            // Random and discarded: nobody can sign in as a demo actor.
            'password' => Str::password(40),
        ]);
        $user->role = $role;
        $user->email_verified_at = now();
        $user->save();

        return $user;
    }

    private function createSupplyChain(): void
    {
        $chain = [
            ['Umurage Mills Ltd', OrganizationType::Manufacturer, 'info@umurage-mills.example', [
                ['UM-PLANT-01', 'Masoro mill', LocationType::Factory, 'Gasabo', 'Masoro industrial zone'],
            ]],
            ['Ubuzima Pharma Ltd', OrganizationType::Manufacturer, 'quality@ubuzima-pharma.example', [
                ['UZ-PLANT-01', 'Kicukiro manufacturing site', LocationType::Factory, 'Kicukiro', 'Gahanga sector'],
            ]],
            ['Ubumwe Coffee Cooperative', OrganizationType::Manufacturer, 'office@ubumwe-coop.example', [
                ['UB-WS-01', 'Huye washing station', LocationType::Factory, 'Huye', 'Maraba sector'],
            ]],
            ['Imena Industries', OrganizationType::Manufacturer, 'sales@imena-industries.example', [
                ['IM-PLANT-01', 'Special Economic Zone plant', LocationType::Factory, 'Gasabo', 'Kigali Special Economic Zone'],
            ]],
            ['Isoko Distribution Co.', OrganizationType::Distributor, 'logistics@isoko.example', [
                ['IS-WH-01', 'Kigali central warehouse', LocationType::Warehouse, 'Nyarugenge', 'Gitega sector'],
                ['IS-DC-02', 'Rubavu distribution centre', LocationType::DistributionCentre, 'Rubavu', 'Gisenyi town'],
            ]],
            ['Musanze Fresh Market', OrganizationType::Retailer, 'hello@musanze-fresh.example', [
                ['MF-SHOP-01', 'Musanze town shop', LocationType::Store, 'Musanze', 'Muhoza sector'],
            ]],
            ['Lake Kivu Pharmacy', OrganizationType::Retailer, 'pharmacy@lakekivu.example', [
                ['KP-SHOP-01', 'Gisenyi pharmacy', LocationType::Store, 'Rubavu', 'Gisenyi sector'],
            ]],
            ['Bugesera Retail Co.', OrganizationType::Retailer, 'shop@bugesera-retail.example', [
                ['BR-SHOP-01', 'Nyamata market shop', LocationType::Store, 'Bugesera', 'Nyamata sector'],
            ]],
        ];

        foreach ($chain as [$name, $type, $email, $locations]) {
            $organization = Organization::create([
                'name' => $name,
                'type' => $type,
                'contact_email' => $email,
            ]);

            foreach ($locations as [$code, $locationName, $locationType, $district, $address]) {
                $this->locations[$code] = $organization->locations()->create([
                    'code' => $code,
                    'name' => $locationName,
                    'type' => $locationType,
                    'district' => $district,
                    'address' => $address,
                ]);
            }
        }
    }

    private function createProducts(): void
    {
        $products = [
            ['UM-MAIZE-25', 'Fortified maize flour 25 kg', ProductCategory::FoodAndBeverages, 'Umurage Mills Ltd', UnitOfMeasure::Bag, 'Maize flour fortified with iron, zinc and vitamins.'],
            ['UM-CASS-10', 'Cassava flour 10 kg', ProductCategory::FoodAndBeverages, 'Umurage Mills Ltd', UnitOfMeasure::Bag, null],
            ['UM-SORG-05', 'Sorghum porridge flour 5 kg', ProductCategory::FoodAndBeverages, 'Umurage Mills Ltd', UnitOfMeasure::Bag, null],
            ['UM-JUICE-1L', 'Pineapple juice 1 l', ProductCategory::FoodAndBeverages, 'Umurage Mills Ltd', UnitOfMeasure::Bottle, 'No added sugar. Store below 25 °C.'],
            ['UZ-PCM-500', 'Paracetamol 500 mg tablets', ProductCategory::Pharmaceuticals, 'Ubuzima Pharma Ltd', UnitOfMeasure::Box, '100 tablets per box.'],
            ['UZ-ORS-20', 'Oral rehydration salts', ProductCategory::Pharmaceuticals, 'Ubuzima Pharma Ltd', UnitOfMeasure::Box, '20 sachets per box.'],
            ['UZ-SAN-500', 'Hand sanitiser 500 ml', ProductCategory::Chemicals, 'Ubuzima Pharma Ltd', UnitOfMeasure::Bottle, '70% alcohol.'],
            ['UB-COFFEE-A', 'Fully washed Arabica coffee (green)', ProductCategory::Agriculture, 'Ubumwe Coffee Cooperative', UnitOfMeasure::Kilogram, 'Bourbon variety, export grade.'],
            ['UB-TEA-BLK', 'CTC black tea', ProductCategory::Agriculture, 'Ubumwe Coffee Cooperative', UnitOfMeasure::Kilogram, null],
            ['IM-SOAP-48', 'Laundry bar soap (carton of 48)', ProductCategory::Cosmetics, 'Imena Industries', UnitOfMeasure::Carton, 'Discontinued product line.'],
        ];

        foreach ($products as [$code, $name, $category, $manufacturer, $unit, $description]) {
            $this->products[$code] = Product::create([
                'product_code' => $code,
                'name' => $name,
                'description' => $description,
                'category' => $category,
                'manufacturer_name' => $manufacturer,
                'unit_of_measure' => $unit,
            ]);
        }
    }

    private function registerBatches(): void
    {
        $register = app(RegisterBatch::class);

        // [batch number, product, origin, days ago produced, days until expiry (null = none), quantity]
        $batches = [
            ['IM-SOAP-2025-040', 'IM-SOAP-48', 'IM-PLANT-01', 125, 400, '200'],
            ['UB-COF-2026-A01', 'UB-COFFEE-A', 'UB-WS-01', 110, 250, '2500.5'],
            ['UM-SORG-2026-007', 'UM-SORG-05', 'UM-PLANT-01', 100, 12, '400'],
            ['UZ-PCM-2026-0098', 'UZ-PCM-500', 'UZ-PLANT-01', 90, 600, '500'],
            ['UM-JUICE-2026-002', 'UM-JUICE-1L', 'UM-PLANT-01', 85, -10, '2000'],
            ['UM-MAIZE-2026-031', 'UM-MAIZE-25', 'UM-PLANT-01', 75, 290, '1200'],
            ['UZ-SAN-2026-005', 'UZ-SAN-500', 'UZ-PLANT-01', 70, 200, '1000'],
            ['UM-CASS-2026-012', 'UM-CASS-10', 'UM-PLANT-01', 60, 120, '600'],
            ['UZ-ORS-2026-021', 'UZ-ORS-20', 'UZ-PLANT-01', 50, 400, '300'],
            ['UB-TEA-2026-B03', 'UB-TEA-BLK', 'UB-WS-01', 45, 500, '1800.25'],
            ['UZ-PCM-2026-0112', 'UZ-PCM-500', 'UZ-PLANT-01', 40, 650, '800'],
            ['UM-JUICE-2026-019', 'UM-JUICE-1L', 'UM-PLANT-01', 30, 25, '1500'],
            ['UM-MAIZE-2026-044', 'UM-MAIZE-25', 'UM-PLANT-01', 20, 345, '900'],
        ];

        foreach ($batches as [$number, $product, $origin, $producedDaysAgo, $expiresInDays, $quantity]) {
            $this->at($producedDaysAgo, 7, 45);

            $this->batches[$number] = $register->handle([
                'product_id' => $this->products[$product]->id,
                'origin_location_id' => $this->locations[$origin]->id,
                'batch_number' => $number,
                'manufacturing_date' => today()->toDateString(),
                'expiry_date' => $expiresInDays === null ? null : $this->today->copy()->addDays($expiresInDays)->toDateString(),
                'initial_quantity' => $quantity,
            ], $this->operations);
        }
    }

    private function moveStock(): void
    {
        // The soap line sold out and was then discontinued.
        $this->remove(118, 'IM-SOAP-2025-040', 'IM-PLANT-01', '200', RemovalReason::Sold);
        $this->at(115);
        $this->products['IM-SOAP-48']->forceFill(['is_active' => false])->save();

        // Maize: mill -> warehouse -> Musanze shop, then sold.
        $this->ship(70, 'UM-PLANT-01', 'IS-WH-01', ['UM-MAIZE-2026-031' => '400'], receiveAfter: 1, notes: 'Truck RAC 482 B');
        $this->ship(60, 'IS-WH-01', 'MF-SHOP-01', ['UM-MAIZE-2026-031' => '150'], receiveAfter: 2);
        $this->remove(55, 'UM-MAIZE-2026-031', 'MF-SHOP-01', '120', RemovalReason::Sold);

        $this->ship(50, 'UM-PLANT-01', 'IS-WH-01', ['UM-CASS-2026-012' => '200', 'UM-SORG-2026-007' => '150'], receiveAfter: 1);

        // Sanitiser to the warehouse, then to the pharmacy.
        $this->ship(45, 'UZ-PLANT-01', 'IS-WH-01', ['UZ-SAN-2026-005' => '300'], receiveAfter: 1);
        $this->ship(40, 'IS-WH-01', 'KP-SHOP-01', ['UZ-SAN-2026-005' => '100'], receiveAfter: 2, notes: 'Delivery note DN-2231');

        // Coffee from the washing station to the export warehouse.
        $this->ship(35, 'UB-WS-01', 'IS-WH-01', ['UB-COF-2026-A01' => '800.5'], receiveAfter: 2, notes: 'For export consolidation');

        // Juice to Rubavu; that stock later expires there.
        $this->ship(30, 'UM-PLANT-01', 'IS-DC-02', ['UM-JUICE-2026-002' => '500'], receiveAfter: 2);

        // Paracetamol batch 0098 reaches the pharmacy before being recalled.
        $this->ship(25, 'UZ-PLANT-01', 'IS-WH-01', ['UZ-PCM-2026-0098' => '200'], receiveAfter: 1);
        $this->ship(22, 'UZ-PLANT-01', 'KP-SHOP-01', ['UZ-ORS-2026-021' => '60'], receiveAfter: 2);
        $this->ship(20, 'IS-WH-01', 'KP-SHOP-01', ['UZ-PCM-2026-0098' => '80'], receiveAfter: 1);

        // A multi-batch delivery to Bugesera.
        $this->ship(15, 'IS-WH-01', 'BR-SHOP-01', ['UM-MAIZE-2026-031' => '100', 'UM-CASS-2026-012' => '60'], receiveAfter: 2, notes: 'Weekly replenishment');

        $this->remove(10, 'UZ-SAN-2026-005', 'KP-SHOP-01', '40', RemovalReason::Sold);
        $this->remove(10, 'UM-CASS-2026-012', 'BR-SHOP-01', '20', RemovalReason::Sold);

        // A shipment cancelled after a breakdown, then sent again.
        $cancelled = $this->ship(8, 'UZ-PLANT-01', 'IS-DC-02', ['UZ-PCM-2026-0112' => '100']);
        $this->at(7, 14, 10);
        app(CancelShipment::class)->handle($cancelled, 'Vehicle breakdown on the Musanze road; rescheduled.', $this->quality);
        $this->ship(6, 'UZ-PLANT-01', 'IS-DC-02', ['UZ-PCM-2026-0112' => '150'], receiveAfter: 1);

        // The recall: the pharmacy reported a quality problem.
        $this->at(5, 11, 0);
        app(RecallBatch::class)->handle(
            $this->batches['UZ-PCM-2026-0098'],
            'Tablet discolouration reported by Gisenyi pharmacy. Laboratory testing in progress; all stock to be quarantined.',
            $this->quality,
        );
        $this->remove(4, 'UZ-PCM-2026-0098', 'KP-SHOP-01', '30', RemovalReason::Disposed, 'Quarantined stock destroyed under supervision.');

        // Expired juice disposed of at Rubavu.
        $this->remove(2, 'UM-JUICE-2026-002', 'IS-DC-02', '200', RemovalReason::Disposed, 'Expired stock removed from sale.');

        // Two shipments still on the road today.
        $this->ship(3, 'UM-PLANT-01', 'IS-WH-01', ['UM-MAIZE-2026-044' => '300'], notes: 'Truck RAD 117 C');
        $this->ship(1, 'UB-WS-01', 'IS-WH-01', ['UB-TEA-2026-B03' => '500.25']);
    }

    /**
     * Dispatch a shipment N days ago and optionally receive it a few days later.
     *
     * @param  array<string, string>  $items  batch number => quantity
     */
    private function ship(int $daysAgo, string $from, string $to, array $items, ?int $receiveAfter = null, ?string $notes = null): Shipment
    {
        $this->at($daysAgo, 8, 15);

        $quantities = collect($items)
            ->mapWithKeys(fn (string $quantity, string $number) => [$this->batches[$number]->id => $quantity])
            ->all();

        $shipment = app(DispatchShipment::class)->handle(
            $this->locations[$from],
            $this->locations[$to],
            $quantities,
            $notes,
            $this->operations,
        );

        if ($receiveAfter !== null) {
            $this->at($daysAgo - $receiveAfter, 15, 40);
            app(ReceiveShipment::class)->handle($shipment, $this->operations);
        }

        return $shipment;
    }

    private function remove(int $daysAgo, string $batch, string $location, string $quantity, RemovalReason $reason, ?string $notes = null): void
    {
        $this->at($daysAgo, 17, 5);

        app(RecordStockRemoval::class)->handle(
            $this->batches[$batch],
            $this->locations[$location],
            $quantity,
            $reason,
            $notes,
            $this->operations,
        );
    }
}

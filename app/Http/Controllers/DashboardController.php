<?php

namespace App\Http\Controllers;

use App\Enums\BatchStatus;
use App\Enums\ShipmentStatus;
use App\Models\Batch;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\StockMovement;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $warningDays = config('productsphere.expiry_warning_days');

        return view('dashboard', [
            'warningDays' => $warningDays,
            'productCount' => Product::count(),
            'activeProductCount' => Product::where('is_active', true)->count(),
            'batchCount' => Batch::count(),
            'expiredCount' => Batch::withStatus(BatchStatus::Expired)->count(),
            'expiringSoonCount' => Batch::expiringWithin($warningDays)->count(),
            'recalledCount' => Batch::withStatus(BatchStatus::Recalled)->count(),
            'inTransitCount' => Shipment::where('status', ShipmentStatus::InTransit)->count(),
            'expiringSoon' => Batch::expiringWithin($warningDays)
                ->with('product')
                ->orderBy('expiry_date')
                ->limit(8)
                ->get(),
            'recentMovements' => StockMovement::query()
                ->with(['batch.product', 'fromLocation', 'toLocation', 'shipment.fromLocation', 'shipment.toLocation', 'user'])
                ->latest('occurred_at')
                ->latest('id')
                ->limit(8)
                ->get(),
        ]);
    }
}

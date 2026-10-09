<?php

namespace App\Http\Controllers;

use App\Enums\BatchStatus;
use App\Models\Batch;
use App\Models\Product;
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
            'expiringSoon' => Batch::expiringWithin($warningDays)
                ->with('product')
                ->orderBy('expiry_date')
                ->limit(8)
                ->get(),
        ]);
    }
}

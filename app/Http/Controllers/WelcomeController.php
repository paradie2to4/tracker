<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Public landing page. Signed-in users go straight to their dashboard.
 */
class WelcomeController extends Controller
{
    public function __invoke(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        // Real figures from the platform, cached briefly so a busy landing
        // page does not run four COUNT queries per visit.
        $stats = Cache::remember('welcome.stats', now()->addMinutes(5), fn () => [
            'batches' => Batch::count(),
            'locations' => Location::count(),
            'shipments' => Shipment::count(),
            'movements' => StockMovement::count(),
        ]);

        return view('welcome', ['stats' => $stats]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Actions\Shipments\DispatchShipment;
use App\Enums\BatchStatus;
use App\Enums\ShipmentStatus;
use App\Http\Requests\StoreShipmentRequest;
use App\Models\Location;
use App\Models\Shipment;
use App\Support\PageSize;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ShipmentController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Shipment::class);

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'status' => ShipmentStatus::tryFrom((string) $request->query('status')),
            'location' => filter_var($request->query('location'), FILTER_VALIDATE_INT) ?: null,
        ];

        $shipments = Shipment::query()
            ->with(['fromLocation', 'toLocation', 'dispatchedBy'])
            ->withCount('items')
            ->when($filters['q'] !== '', fn ($query) => $query->whereKey(Shipment::idFromReference($filters['q']) ?? 0))
            ->when($filters['status'], fn ($query, ShipmentStatus $status) => $query->where('status', $status))
            ->when($filters['location'], fn ($query, int $locationId) => $query->where(fn ($query) => $query
                ->where('from_location_id', $locationId)
                ->orWhere('to_location_id', $locationId)))
            ->latest('dispatched_at')
            ->latest('id')
            ->paginate(PageSize::for($request))
            ->withQueryString();

        return view('shipments.index', [
            'shipments' => $shipments,
            'filters' => $filters,
            'statuses' => ShipmentStatus::cases(),
            'locations' => Location::with('organization')->orderBy('code')->get(),
        ]);
    }

    /**
     * Two steps without JavaScript: first choose the origin, then the form
     * lists only the batches that actually have stock at that origin.
     */
    public function create(Request $request): View
    {
        Gate::authorize('create', Shipment::class);

        $locations = Location::where('is_active', true)->with('organization')->orderBy('code')->get();
        $from = $locations->firstWhere('id', $request->integer('from'));

        $balances = $from === null ? collect() : $from->stockBalances()
            ->where('quantity', '>', 0)
            ->with('batch.product')
            ->orderBy('batch_id')
            ->get()
            // Recalled and expired stock cannot be shipped; listing it would
            // only produce errors on submit.
            ->filter(fn ($balance) => ! in_array($balance->batch->status, [BatchStatus::Recalled, BatchStatus::Expired], true));

        return view('shipments.create', [
            'locations' => $locations,
            'from' => $from,
            'balances' => $balances,
        ]);
    }

    public function store(StoreShipmentRequest $request, DispatchShipment $dispatch): RedirectResponse
    {
        $shipment = $dispatch->handle(
            Location::findOrFail($request->integer('from_location_id')),
            Location::findOrFail($request->integer('to_location_id')),
            $request->quantities(),
            $request->validated('notes'),
            $request->user(),
        );

        return redirect()
            ->route('shipments.show', $shipment)
            ->with('success', "Shipment {$shipment->reference} has been dispatched and is now in transit.");
    }

    public function show(Shipment $shipment): View
    {
        Gate::authorize('view', $shipment);

        $shipment->load([
            'fromLocation.organization',
            'toLocation.organization',
            'items.batch.product',
            'dispatchedBy',
            'receivedBy',
            'cancelledBy',
        ]);

        return view('shipments.show', ['shipment' => $shipment]);
    }
}

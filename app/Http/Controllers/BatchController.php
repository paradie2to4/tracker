<?php

namespace App\Http\Controllers;

use App\Actions\Batches\RegisterBatch;
use App\Enums\BatchStatus;
use App\Enums\RemovalReason;
use App\Enums\ShipmentStatus;
use App\Http\Requests\StoreBatchRequest;
use App\Http\Requests\UpdateBatchRequest;
use App\Models\Batch;
use App\Models\Location;
use App\Models\Product;
use App\Models\ShipmentItem;
use App\Support\PageSize;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BatchController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Batch::class);

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'product' => filter_var($request->query('product'), FILTER_VALIDATE_INT) ?: null,
            'status' => BatchStatus::tryFrom((string) $request->query('status')),
        ];

        $batches = Batch::query()
            ->with('product')
            ->when($filters['q'] !== '', fn ($query) => $query->whereLike('batch_number', "%{$filters['q']}%"))
            ->when($filters['product'], fn ($query, int $productId) => $query->where('product_id', $productId))
            ->when($filters['status'], fn ($query, BatchStatus $status) => $query->withStatus($status))
            ->orderByDesc('manufacturing_date')
            ->orderByDesc('id')
            ->paginate(PageSize::for($request))
            ->withQueryString();

        return view('batches.index', [
            'batches' => $batches,
            'filters' => $filters,
            'statuses' => BatchStatus::cases(),
            'products' => Product::orderBy('name')->get(['id', 'name', 'product_code']),
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Batch::class);

        return view('batches.create', [
            'batch' => new Batch(['product_id' => $request->integer('product') ?: null]),
            'products' => Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'product_code', 'unit_of_measure']),
            'locations' => Location::where('is_active', true)->with('organization')->orderBy('code')->get(),
        ]);
    }

    public function store(StoreBatchRequest $request, RegisterBatch $register): RedirectResponse
    {
        $batch = $register->handle($request->validated(), $request->user());

        return redirect()
            ->route('batches.show', $batch)
            ->with('success', "Batch {$batch->batch_number} has been registered.");
    }

    /**
     * The traceability view of a batch: where its stock is now, what is in
     * transit, and every movement that got it there.
     */
    public function show(Batch $batch): View
    {
        Gate::authorize('view', $batch);

        $batch->load(['product', 'recalledBy', 'originLocation.organization']);

        $balances = $batch->stockBalances()
            ->where('quantity', '>', 0)
            ->with('location.organization')
            ->orderByDesc('quantity')
            ->get();

        $inTransit = ShipmentItem::query()
            ->where('batch_id', $batch->getKey())
            ->whereHas('shipment', fn ($query) => $query->where('status', ShipmentStatus::InTransit))
            ->with('shipment.fromLocation', 'shipment.toLocation')
            ->get();

        return view('batches.show', [
            'batch' => $batch,
            'balances' => $balances,
            'inTransit' => $inTransit,
            'movements' => $batch->stockMovements()
                ->with(['fromLocation', 'toLocation', 'shipment.fromLocation', 'shipment.toLocation', 'user'])
                ->latest('occurred_at')
                ->latest('id')
                ->paginate(PageSize::for(request(), PageSize::NESTED), pageName: 'history'),
            'removalReasons' => RemovalReason::cases(),
            'activeLocations' => $batch->origin_location_id === null
                ? Location::where('is_active', true)->with('organization')->orderBy('code')->get()
                : collect(),
        ]);
    }

    public function edit(Batch $batch): View
    {
        Gate::authorize('update', $batch);

        $batch->load('product');

        return view('batches.edit', ['batch' => $batch]);
    }

    public function update(UpdateBatchRequest $request, Batch $batch): RedirectResponse
    {
        $batch->update($request->validated());

        return redirect()
            ->route('batches.show', $batch)
            ->with('success', 'Batch details have been updated.');
    }
}

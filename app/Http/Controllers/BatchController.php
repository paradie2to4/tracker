<?php

namespace App\Http\Controllers;

use App\Enums\BatchStatus;
use App\Http\Requests\StoreBatchRequest;
use App\Http\Requests\UpdateBatchRequest;
use App\Models\Batch;
use App\Models\Product;
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
            ->paginate(15)
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
        ]);
    }

    public function store(StoreBatchRequest $request): RedirectResponse
    {
        $data = $request->validated();
        // A new batch starts with all of its stock available.
        $data['current_quantity'] = $data['initial_quantity'];

        $batch = Batch::create($data);

        return redirect()
            ->route('batches.show', $batch)
            ->with('success', "Batch {$batch->batch_number} has been registered.");
    }

    public function show(Batch $batch): View
    {
        Gate::authorize('view', $batch);

        $batch->load(['product', 'recalledBy']);

        return view('batches.show', ['batch' => $batch]);
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

<x-layouts.app :title="$product->name">
    <x-slot:actions>
        @can('update', $product)
            <a href="{{ route('products.edit', $product) }}" class="btn btn-secondary">Edit</a>
        @endcan
        @can('changeStatus', $product)
            <form method="POST" action="{{ route('products.status', $product) }}"
                  data-confirm="{{ $product->is_active
                      ? 'Deactivate this product? No new batches can be registered for it until it is reactivated.'
                      : 'Reactivate this product?' }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="is_active" value="{{ $product->is_active ? 0 : 1 }}">
                <button type="submit" class="btn {{ $product->is_active ? 'btn-danger' : 'btn-secondary' }}">
                    {{ $product->is_active ? 'Deactivate' : 'Activate' }}
                </button>
            </form>
        @endcan
        @if ($product->is_active)
            @can('create', App\Models\Batch::class)
                <a href="{{ route('batches.create', ['product' => $product->id]) }}" class="btn btn-primary">Register batch</a>
            @endcan
        @endif
    </x-slot:actions>

    <section class="card" aria-labelledby="details-heading">
        <h2 id="details-heading" class="sr-only">Product details</h2>
        <dl class="grid grid-cols-1 gap-x-6 gap-y-5 p-4 sm:grid-cols-2 sm:p-6 lg:grid-cols-3">
            <div>
                <dt class="text-sm text-slate-500">Product code</dt>
                <dd class="mt-1 font-mono text-sm font-medium text-slate-900">{{ $product->product_code }}</dd>
            </div>
            <div>
                <dt class="text-sm text-slate-500">Status</dt>
                <dd class="mt-1"><x-product-status :active="$product->is_active" /></dd>
            </div>
            <div>
                <dt class="text-sm text-slate-500">Category</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $product->category->label() }}</dd>
            </div>
            <div>
                <dt class="text-sm text-slate-500">Manufacturer</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $product->manufacturer_name }}</dd>
            </div>
            <div>
                <dt class="text-sm text-slate-500">Unit of measure</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $product->unit_of_measure->label() }}</dd>
            </div>
            <div>
                <dt class="text-sm text-slate-500">Registered</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $product->created_at->format('d M Y, H:i') }}</dd>
            </div>
            <div class="sm:col-span-2 lg:col-span-3">
                <dt class="text-sm text-slate-500">Description</dt>
                <dd class="mt-1 text-sm whitespace-pre-line text-slate-900">{{ $product->description ?: '—' }}</dd>
            </div>
        </dl>
    </section>

    <section class="card mt-8" aria-labelledby="batches-heading">
        <div class="border-b border-slate-200 px-4 py-4 sm:px-6">
            <h2 id="batches-heading" class="text-base font-semibold text-slate-900">Batches</h2>
            <p class="mt-1 text-sm text-slate-500">{{ number_format($batches->total()) }} {{ Str::plural('batch', $batches->total()) }} registered for this product.</p>
        </div>

        @if ($batches->isEmpty())
            <x-empty-state title="No batches yet"
                           :message="$product->is_active ? 'Register a batch to start tracking this product.' : 'This product is inactive, so new batches cannot be registered.'">
                @if ($product->is_active)
                    <a href="{{ route('batches.create', ['product' => $product->id]) }}" class="btn btn-primary">Register batch</a>
                @endif
            </x-empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="table-header">Batch number</th>
                            <th scope="col" class="table-header">Manufactured</th>
                            <th scope="col" class="table-header">Expires</th>
                            <th scope="col" class="table-header text-right">Current / initial ({{ $product->unit_of_measure->value }})</th>
                            <th scope="col" class="table-header">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($batches as $batch)
                            <tr class="hover:bg-slate-50">
                                <td class="table-cell"><a href="{{ route('batches.show', $batch) }}" class="link font-mono">{{ $batch->batch_number }}</a></td>
                                <td class="table-cell">{{ $batch->manufacturing_date->format('d M Y') }}</td>
                                <td class="table-cell">{{ $batch->expiry_date?->format('d M Y') ?? 'No expiry' }}</td>
                                <td class="table-cell text-right tabular-nums">
                                    {{ App\Support\Quantity::format($batch->current_quantity) }} / {{ App\Support\Quantity::format($batch->initial_quantity) }}
                                </td>
                                <td class="table-cell"><x-batch-status :status="$batch->status" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($batches->hasPages())
                <div class="border-t border-slate-200 px-4 py-3">{{ $batches->links() }}</div>
            @endif
        @endif
    </section>
</x-layouts.app>

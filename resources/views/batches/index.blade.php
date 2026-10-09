<x-layouts.app title="Batches">
    <x-slot:actions>
        @can('create', App\Models\Batch::class)
            <a href="{{ route('batches.create') }}" class="btn btn-primary">Register batch</a>
        @endcan
    </x-slot:actions>

    @php($filtered = $filters['q'] !== '' || $filters['product'] || $filters['status'])

    <form method="GET" action="{{ route('batches.index') }}" role="search" class="card mb-6 grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 lg:grid-cols-[2fr_2fr_1fr_auto] lg:items-end">
        <div>
            <label for="q" class="block text-sm font-medium text-ink-700">Batch number</label>
            <input type="search" id="q" name="q" value="{{ $filters['q'] }}" placeholder="Search by batch number" maxlength="50" class="form-control mt-1.5">
        </div>
        <div>
            <label for="product" class="block text-sm font-medium text-ink-700">Product</label>
            <select id="product" name="product" class="form-control mt-1.5">
                <option value="">All products</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" @selected($filters['product'] === $product->id)>{{ $product->product_code }} — {{ $product->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="status" class="block text-sm font-medium text-ink-700">Status</label>
            <select id="status" name="status" class="form-control mt-1.5">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected($filters['status'] === $status)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary">Filter</button>
            @if ($filtered)
                <a href="{{ route('batches.index') }}" class="btn btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    <div class="card">
        @if ($batches->isEmpty())
            @if ($filtered)
                <x-empty-state title="No matching batches" message="Try a different batch number or clear the filters." />
            @else
                <x-empty-state title="No batches yet" message="Register a batch for one of your products to start tracking stock and expiry.">
                    <a href="{{ route('batches.create') }}" class="btn btn-primary">Register batch</a>
                </x-empty-state>
            @endif
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-200">
                    <thead class="bg-ink-50">
                        <tr>
                            <th scope="col" class="table-header">Batch number</th>
                            <th scope="col" class="table-header">Product</th>
                            <th scope="col" class="table-header">Manufactured</th>
                            <th scope="col" class="table-header">Expires</th>
                            <th scope="col" class="table-header text-right">Current quantity</th>
                            <th scope="col" class="table-header">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100">
                        @foreach ($batches as $batch)
                            <tr class="hover:bg-ink-50">
                                <td class="table-cell"><a href="{{ route('batches.show', $batch) }}" class="link font-mono">{{ $batch->batch_number }}</a></td>
                                <td class="table-cell">
                                    <a href="{{ route('products.show', $batch->product) }}" class="hover:underline">{{ $batch->product->name }}</a>
                                    <span class="block font-mono text-xs text-ink-500">{{ $batch->product->product_code }}</span>
                                </td>
                                <td class="table-cell">{{ $batch->manufacturing_date->format('d M Y') }}</td>
                                <td class="table-cell">
                                    {{ $batch->expiry_date?->format('d M Y') ?? 'No expiry' }}
                                    @if ($batch->isApproachingExpiry())
                                        <span class="block text-xs font-medium text-honey-700">Expires in {{ $batch->daysUntilExpiry() }} {{ Str::plural('day', $batch->daysUntilExpiry()) }}</span>
                                    @endif
                                </td>
                                <td class="table-cell text-right tabular-nums">{{ App\Support\Quantity::format($batch->current_quantity) }} {{ $batch->product->unit_of_measure->value }}</td>
                                <td class="table-cell"><x-batch-status :status="$batch->status" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($batches->hasPages())
                <div class="border-t border-ink-200 px-4 py-3">{{ $batches->onEachSide(1)->links() }}</div>
            @endif
        @endif
    </div>
</x-layouts.app>

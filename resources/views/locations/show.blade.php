<x-layouts.app :title="$location->name">
    <x-slot:actions>
        @can('update', $location)
            <a href="{{ route('locations.edit', $location) }}" class="btn btn-secondary">Edit</a>
        @endcan
        @if ($location->is_active)
            @can('create', App\Models\Shipment::class)
                <a href="{{ route('shipments.create', ['from' => $location->id]) }}" class="btn btn-primary">Ship from here</a>
            @endcan
        @endif
    </x-slot:actions>

    <section class="card" aria-labelledby="location-details-heading">
        <h2 id="location-details-heading" class="sr-only">Location details</h2>
        <dl class="grid grid-cols-1 gap-x-6 gap-y-5 p-4 sm:grid-cols-2 sm:p-6 lg:grid-cols-3">
            <div>
                <dt class="text-sm text-ink-500">Code</dt>
                <dd class="mt-1 font-mono text-sm font-medium text-ink-900">{{ $location->code }}</dd>
            </div>
            <div>
                <dt class="text-sm text-ink-500">Organisation</dt>
                <dd class="mt-1 text-sm"><a href="{{ route('organizations.show', $location->organization) }}" class="link">{{ $location->organization->name }}</a></dd>
            </div>
            <div>
                <dt class="text-sm text-ink-500">Status</dt>
                <dd class="mt-1"><x-product-status :active="$location->is_active" /></dd>
            </div>
            <div>
                <dt class="text-sm text-ink-500">Type</dt>
                <dd class="mt-1 text-sm text-ink-900">{{ $location->type->label() }}</dd>
            </div>
            <div>
                <dt class="text-sm text-ink-500">District</dt>
                <dd class="mt-1 text-sm text-ink-900">{{ $location->district ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-sm text-ink-500">Address</dt>
                <dd class="mt-1 text-sm text-ink-900">{{ $location->address ?? '—' }}</dd>
            </div>
        </dl>
    </section>

    <section class="card mt-8" aria-labelledby="stock-heading">
        <div class="border-b border-ink-200 px-4 py-4 sm:px-6">
            <h2 id="stock-heading" class="text-base font-semibold text-ink-900">Stock on hand</h2>
        </div>

        @if ($balances->isEmpty())
            <x-empty-state title="No stock at this location" message="Stock arrives here when a batch is produced here or a shipment is received." />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-200">
                    <thead class="bg-ink-50">
                        <tr>
                            <th scope="col" class="table-header">Batch</th>
                            <th scope="col" class="table-header">Product</th>
                            <th scope="col" class="table-header">Expires</th>
                            <th scope="col" class="table-header text-right">Quantity</th>
                            <th scope="col" class="table-header">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100">
                        @foreach ($balances as $balance)
                            <tr class="hover:bg-ink-50">
                                <td class="table-cell"><a href="{{ route('batches.show', $balance->batch) }}" class="link font-mono">{{ $balance->batch->batch_number }}</a></td>
                                <td class="table-cell">{{ $balance->batch->product->name }}</td>
                                <td class="table-cell">{{ $balance->batch->expiry_date?->format('d M Y') ?? 'No expiry' }}</td>
                                <td class="table-cell text-right tabular-nums">{{ App\Support\Quantity::format($balance->quantity) }} {{ $balance->batch->product->unit_of_measure->value }}</td>
                                <td class="table-cell"><x-batch-status :status="$balance->batch->status" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($balances->hasPages())
                <div class="border-t border-ink-200 px-4 py-3">{{ $balances->links() }}</div>
            @endif
        @endif
    </section>

    <section class="card mt-8" aria-labelledby="location-movements-heading">
        <div class="border-b border-ink-200 px-4 py-4 sm:px-6">
            <h2 id="location-movements-heading" class="text-base font-semibold text-ink-900">Recent movements</h2>
            <p class="mt-1 text-sm text-ink-500">The 20 most recent stock movements in or out of this location.</p>
        </div>

        @if ($movements->isEmpty())
            <x-empty-state title="No movements yet" />
        @else
            <ul class="divide-y divide-ink-100">
                @foreach ($movements as $movement)
                    <li class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 px-4 py-3 text-sm sm:px-6">
                        <div class="min-w-0 text-ink-700">
                            <a href="{{ route('batches.show', $movement->batch) }}" class="link font-mono">{{ $movement->batch->batch_number }}</a>
                            · <x-movement-description :movement="$movement" />
                        </div>
                        <div class="flex items-baseline gap-4 text-ink-500">
                            <span class="tabular-nums font-medium {{ $movement->type->direction() > 0 ? 'text-brand-700' : 'text-clay-700' }}">
                                {{ $movement->type->direction() > 0 ? '+' : '−' }}{{ App\Support\Quantity::format($movement->quantity) }}
                            </span>
                            <time datetime="{{ $movement->occurred_at->toIso8601String() }}">{{ $movement->occurred_at->format('d M Y, H:i') }}</time>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-layouts.app>

<x-layouts.app title="Dashboard">
    <section class="relative mb-8 overflow-hidden rounded-2xl bg-ink-950 px-6 py-7 text-white sm:px-8" aria-labelledby="welcome-heading">
        <div class="bg-grid-dark absolute inset-0" aria-hidden="true"></div>
        <div class="absolute -top-24 -right-16 size-72 rounded-full bg-brand-600/40 blur-3xl" aria-hidden="true"></div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-2xl">
                <h2 id="welcome-heading" class="text-2xl font-extrabold tracking-tight">Welcome, {{ Str::before(auth()->user()->name, ' ') }}</h2>
                <p class="mt-2 text-ink-200">
                    This is your supply chain at a glance. Open any batch to see where its stock is right now and every
                    movement that got it there, or follow a recalled batch to see which locations are affected.
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('batches.index', ['status' => 'recalled']) }}" class="btn btn-accent">Trace a recalled batch</a>
                <a href="{{ route('shipments.create') }}" class="btn btn-ghost-dark">New shipment</a>
            </div>
        </div>
    </section>

    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <x-stat-card label="Registered products" :value="$productCount"
                     :description="number_format($activeProductCount).' active'"
                     :href="route('products.index')" />
        <x-stat-card label="Registered batches" :value="$batchCount"
                     :description="number_format($recalledCount).' recalled'"
                     :href="route('batches.index')" />
        <x-stat-card label="Expired batches" :value="$expiredCount" :tone="$expiredCount > 0 ? 'danger' : 'default'"
                     description="Past expiry date with stock remaining"
                     :href="route('batches.index', ['status' => 'expired'])" />
        <x-stat-card label="Approaching expiry" :value="$expiringSoonCount" :tone="$expiringSoonCount > 0 ? 'warning' : 'default'"
                     :description="'Active batches expiring within '.$warningDays.' days'" />
        <x-stat-card label="Shipments in transit" :value="$inTransitCount" tone="info"
                     description="Dispatched, awaiting receipt"
                     :href="route('shipments.index', ['status' => 'in_transit'])" />
    </dl>

    <section class="card mt-8" aria-labelledby="expiring-heading">
        <div class="border-b border-ink-200 px-4 py-4 sm:px-6">
            <h2 id="expiring-heading" class="text-base font-semibold text-ink-900">Expiry warnings</h2>
            <p class="mt-1 text-sm text-ink-500">Active batches that expire within the next {{ $warningDays }} days, soonest first.</p>
        </div>

        @if ($expiringSoon->isEmpty())
            <x-empty-state title="No batches are approaching expiry"
                           :message="'No active batch expires in the next '.$warningDays.' days.'" />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-200">
                    <thead class="bg-ink-50">
                        <tr>
                            <th scope="col" class="table-header">Batch</th>
                            <th scope="col" class="table-header">Product</th>
                            <th scope="col" class="table-header">Expiry date</th>
                            <th scope="col" class="table-header">Remaining</th>
                            <th scope="col" class="table-header text-right">Current quantity</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100">
                        @foreach ($expiringSoon as $batch)
                            @php($days = $batch->daysUntilExpiry())
                            <tr>
                                <td class="table-cell"><a href="{{ route('batches.show', $batch) }}" class="link font-mono">{{ $batch->batch_number }}</a></td>
                                <td class="table-cell">{{ $batch->product->name }}</td>
                                <td class="table-cell">{{ $batch->expiry_date->format('d M Y') }}</td>
                                <td class="table-cell">
                                    <span class="font-medium {{ $days <= 7 ? 'text-red-700' : 'text-amber-700' }}">
                                        {{ $days === 0 ? 'Expires today' : $days.' '.Str::plural('day', $days) }}
                                    </span>
                                </td>
                                <td class="table-cell text-right tabular-nums">{{ App\Support\Quantity::format($batch->current_quantity) }} {{ $batch->product->unit_of_measure->value }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="card mt-8" aria-labelledby="activity-heading">
        <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-ink-200 px-4 py-4 sm:px-6">
            <div>
                <h2 id="activity-heading" class="text-base font-semibold text-ink-900">Recent activity</h2>
                <p class="mt-1 text-sm text-ink-500">The latest stock movements across the supply chain.</p>
            </div>
            <a href="{{ route('shipments.index') }}" class="link text-sm">All shipments →</a>
        </div>

        @if ($recentMovements->isEmpty())
            <x-empty-state title="No stock movements yet" message="Register a batch or dispatch a shipment to see activity here." />
        @else
            <ul class="divide-y divide-ink-100">
                @foreach ($recentMovements as $movement)
                    <li class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 px-4 py-3 text-sm sm:px-6">
                        <div class="min-w-0 text-ink-700">
                            <a href="{{ route('batches.show', $movement->batch) }}" class="link font-mono">{{ $movement->batch->batch_number }}</a>
                            <span class="text-ink-400">· {{ $movement->batch->product->name }}</span>
                            <p class="mt-0.5"><x-movement-description :movement="$movement" /></p>
                        </div>
                        <div class="flex items-baseline gap-4 text-ink-500">
                            <span class="font-semibold tabular-nums {{ $movement->type->direction() > 0 ? 'text-emerald-700' : 'text-red-700' }}">
                                {{ $movement->type->direction() > 0 ? '+' : '−' }}{{ App\Support\Quantity::format($movement->quantity) }}
                            </span>
                            <time datetime="{{ $movement->occurred_at->toIso8601String() }}" title="{{ $movement->occurred_at->format('d M Y, H:i') }}">{{ $movement->occurred_at->diffForHumans() }}</time>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-layouts.app>

@php($unit = $batch->product->unit_of_measure->value)

<x-layouts.app :title="'Batch '.$batch->batch_number">
    <x-slot:actions>
        @can('update', $batch)
            <a href="{{ route('batches.edit', $batch) }}" class="btn btn-secondary">Edit</a>
        @endcan
    </x-slot:actions>

    @if ($batch->isRecalled())
        <div role="alert" class="mb-6 rounded-md bg-red-50 p-4 ring-1 ring-red-200">
            <h2 class="text-sm font-semibold text-red-800">This batch has been recalled</h2>
            <p class="mt-1 text-sm text-red-700">
                Recalled on {{ $batch->recalled_at->format('d M Y, H:i') }}{{ $batch->recalledBy ? ' by '.$batch->recalledBy->name : '' }}.
                Recalled batches can no longer be edited.
            </p>
            <p class="mt-2 text-sm whitespace-pre-line text-red-800"><span class="font-medium">Reason:</span> {{ $batch->recall_reason }}</p>
        </div>
    @elseif ($batch->isApproachingExpiry())
        <div role="status" class="mb-6 rounded-md bg-amber-50 px-4 py-3 text-sm text-amber-800 ring-1 ring-amber-200">
            @php($days = $batch->daysUntilExpiry())
            {{ $days === 0 ? 'This batch expires today.' : "This batch expires in {$days} ".Str::plural('day', $days).'.' }}
        </div>
    @endif

    <section class="card" aria-labelledby="batch-details-heading">
        <h2 id="batch-details-heading" class="sr-only">Batch details</h2>
        <dl class="grid grid-cols-1 gap-x-6 gap-y-5 p-4 sm:grid-cols-2 sm:p-6 lg:grid-cols-3">
            <div>
                <dt class="text-sm text-ink-500">Batch number</dt>
                <dd class="mt-1 font-mono text-sm font-medium text-ink-900">{{ $batch->batch_number }}</dd>
            </div>
            <div>
                <dt class="text-sm text-ink-500">Status</dt>
                <dd class="mt-1"><x-batch-status :status="$batch->status" /></dd>
            </div>
            <div>
                <dt class="text-sm text-ink-500">Product</dt>
                <dd class="mt-1 text-sm">
                    <a href="{{ route('products.show', $batch->product) }}" class="link">{{ $batch->product->name }}</a>
                    <span class="block font-mono text-xs text-ink-500">{{ $batch->product->product_code }}</span>
                </dd>
            </div>
            <div>
                <dt class="text-sm text-ink-500">Manufacturing date</dt>
                <dd class="mt-1 text-sm text-ink-900">{{ $batch->manufacturing_date->format('d M Y') }}</dd>
            </div>
            <div>
                <dt class="text-sm text-ink-500">Expiry date</dt>
                <dd class="mt-1 text-sm text-ink-900">{{ $batch->expiry_date?->format('d M Y') ?? 'No expiry' }}</dd>
            </div>
            <div>
                <dt class="text-sm text-ink-500">Registered</dt>
                <dd class="mt-1 text-sm text-ink-900">{{ $batch->created_at->format('d M Y, H:i') }}</dd>
            </div>
            <div>
                <dt class="text-sm text-ink-500">Initial quantity</dt>
                <dd class="mt-1 text-sm tabular-nums text-ink-900">{{ App\Support\Quantity::format($batch->initial_quantity) }} {{ $unit }}</dd>
            </div>
            <div>
                <dt class="text-sm text-ink-500">Current quantity</dt>
                <dd class="mt-1 text-sm tabular-nums text-ink-900">{{ App\Support\Quantity::format($batch->current_quantity) }} {{ $unit }}</dd>
            </div>
            <div>
                <dt class="text-sm text-ink-500">Produced at</dt>
                <dd class="mt-1 text-sm text-ink-900">
                    @if ($batch->originLocation)
                        <a href="{{ route('locations.show', $batch->originLocation) }}" class="link">{{ $batch->originLocation->name }}</a>
                        <span class="block text-xs text-ink-500">{{ $batch->originLocation->organization->name }}</span>
                    @else
                        <span class="text-ink-500">Not recorded</span>
                    @endif
                </dd>
            </div>
        </dl>
    </section>

    @if ($batch->origin_location_id === null)
        <section class="card mt-8 max-w-3xl border-l-4 border-amber-500" aria-labelledby="opening-heading">
            <div class="p-4 sm:p-6">
                <h2 id="opening-heading" class="text-base font-semibold text-ink-900">Stock location not recorded</h2>
                <p class="mt-1 text-sm text-ink-600">
                    This batch was registered before stock tracking was introduced, so its
                    {{ App\Support\Quantity::format($batch->current_quantity) }} {{ $unit }} are not assigned to any location
                    and cannot be shipped yet.
                </p>

                @can('assignOpeningStock', $batch)
                    <form method="POST" action="{{ route('batches.opening-stock.store', $batch) }}" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end" novalidate
                          data-confirm="Place the current stock of {{ $batch->batch_number }} at the selected location? This can only be done once.">
                        @csrf
                        <div class="flex-1">
                            <x-form.select name="location_id" label="Where is this stock now?" required
                                           :options="$activeLocations->mapWithKeys(fn ($l) => [$l->id => $l->label().' ('.$l->organization->name.')'])->all()" />
                        </div>
                        <button type="submit" class="btn btn-primary">Assign opening stock</button>
                    </form>
                @else
                    <p class="mt-3 text-sm text-ink-500">An administrator can assign its current location.</p>
                @endcan
            </div>
        </section>
    @else
        <section class="card mt-8" aria-labelledby="stock-heading">
            <div class="border-b border-ink-200 px-4 py-4 sm:px-6">
                <h2 id="stock-heading" class="text-base font-semibold text-ink-900">Where the stock is now</h2>
            </div>

            @if ($balances->isEmpty() && $inTransit->isEmpty())
                <x-empty-state title="No stock remaining" message="All of this batch has left the supply chain." />
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-ink-200">
                        <thead class="bg-ink-50">
                            <tr>
                                <th scope="col" class="table-header">Location</th>
                                <th scope="col" class="table-header">Organisation</th>
                                <th scope="col" class="table-header text-right">Quantity ({{ $unit }})</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100">
                            @foreach ($balances as $balance)
                                <tr>
                                    <td class="table-cell"><a href="{{ route('locations.show', $balance->location) }}" class="link">{{ $balance->location->name }}</a></td>
                                    <td class="table-cell">{{ $balance->location->organization->name }}</td>
                                    <td class="table-cell text-right tabular-nums">{{ App\Support\Quantity::format($balance->quantity) }}</td>
                                </tr>
                            @endforeach
                            @foreach ($inTransit as $item)
                                <tr class="bg-sky-50/50">
                                    <td class="table-cell" colspan="2">
                                        In transit:
                                        <a href="{{ route('shipments.show', $item->shipment) }}" class="link font-mono">{{ $item->shipment->reference }}</a>
                                        ({{ $item->shipment->fromLocation->name }} → {{ $item->shipment->toLocation->name }})
                                    </td>
                                    <td class="table-cell text-right tabular-nums">{{ App\Support\Quantity::format($item->quantity) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t border-ink-200 bg-ink-50">
                            <tr>
                                <th scope="row" colspan="2" class="table-cell text-left font-semibold">Total in the supply chain</th>
                                <td class="table-cell text-right font-semibold tabular-nums">{{ App\Support\Quantity::format($batch->current_quantity) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </section>

        <section class="card mt-8" aria-labelledby="history-heading">
            <div class="border-b border-ink-200 px-4 py-4 sm:px-6">
                <h2 id="history-heading" class="text-base font-semibold text-ink-900">Movement history</h2>
                <p class="mt-1 text-sm text-ink-500">Every change to this batch's stock, newest first. Entries cannot be edited or deleted.</p>
            </div>

            @if ($movements->isEmpty())
                <x-empty-state title="No movements recorded" />
            @else
                <ul class="divide-y divide-ink-100">
                    @foreach ($movements as $movement)
                        <li class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 px-4 py-3 text-sm sm:px-6">
                            <div class="min-w-0 text-ink-700">
                                <x-movement-description :movement="$movement" />
                                @if ($movement->notes)
                                    <p class="mt-0.5 text-xs text-ink-500">{{ $movement->notes }}</p>
                                @endif
                            </div>
                            <div class="flex items-baseline gap-4 text-ink-500">
                                <span class="tabular-nums font-medium {{ $movement->type->direction() > 0 ? 'text-emerald-700' : 'text-red-700' }}">
                                    {{ $movement->type->direction() > 0 ? '+' : '−' }}{{ App\Support\Quantity::format($movement->quantity) }}
                                </span>
                                <span>
                                    <time datetime="{{ $movement->occurred_at->toIso8601String() }}">{{ $movement->occurred_at->format('d M Y, H:i') }}</time>
                                    @if ($movement->user) · {{ $movement->user->name }} @endif
                                </span>
                            </div>
                        </li>
                    @endforeach
                </ul>

                @if ($movements->hasPages())
                    <div class="border-t border-ink-200 px-4 py-3">{{ $movements->links() }}</div>
                @endif
            @endif
        </section>

        @can('removeStock', $batch)
            @if ($balances->isNotEmpty())
                <section class="card mt-8 max-w-3xl" aria-labelledby="removal-heading">
                    <form method="POST" action="{{ route('batches.removals.store', $batch) }}" class="p-4 sm:p-6" novalidate
                          data-confirm="Remove this quantity from stock? Removals cannot be undone.">
                        @csrf
                        <h2 id="removal-heading" class="text-base font-semibold text-ink-900">Remove stock</h2>
                        <p class="mt-1 text-sm text-ink-500">Record stock that has left the supply chain: sold to end customers, used, damaged, disposed of or lost.</p>

                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <x-form.select name="location_id" label="Location" required
                                           :options="$balances->mapWithKeys(fn ($b) => [$b->location_id => $b->location->name.' ('.App\Support\Quantity::format($b->quantity).' '.$unit.')'])->all()" />
                            <x-form.input type="number" name="quantity" :label="'Quantity ('.$unit.')'" required step="0.001" min="0.001" inputmode="decimal" />
                            <div class="sm:col-span-2">
                                <x-form.select name="reason" label="Reason" required
                                               :options="collect($removalReasons)->mapWithKeys(fn ($r) => [$r->value => $r->label()])->all()" />
                            </div>
                            <div class="sm:col-span-2">
                                <x-form.textarea name="notes" label="Notes" rows="2" maxlength="1000"
                                                 hint="Required for losses and stock count corrections." />
                            </div>
                        </div>

                        <div class="mt-4 flex justify-end">
                            <button type="submit" class="btn btn-secondary">Record removal</button>
                        </div>
                    </form>
                </section>
            @endif
        @endcan
    @endif

    @can('recall', $batch)
        <section class="card mt-8 max-w-3xl border-l-4 border-red-500" aria-labelledby="recall-heading">
            <form method="POST" action="{{ route('batches.recall', $batch) }}" class="p-4 sm:p-6" novalidate
                  data-confirm="Recall batch {{ $batch->batch_number }}? This cannot be undone.">
                @csrf
                <h2 id="recall-heading" class="text-base font-semibold text-ink-900">Recall this batch</h2>
                <p class="mt-1 text-sm text-ink-500">Marks the batch as recalled: it can no longer be shipped or edited, though stock can still be removed for disposal. This cannot be undone.</p>

                <div class="mt-4">
                    <x-form.textarea name="recall_reason" label="Reason for recall" required rows="3" maxlength="1000"
                                     hint="At least 10 characters, e.g. the quality issue found and who reported it." />
                </div>

                <div class="mt-4 flex justify-end">
                    <button type="submit" class="btn btn-danger">Recall batch</button>
                </div>
            </form>
        </section>
    @endcan
</x-layouts.app>

<x-layouts.app :title="'Shipment '.$shipment->reference">
    <x-slot:actions>
        @can('receive', $shipment)
            <form method="POST" action="{{ route('shipments.receipt.store', $shipment) }}"
                  data-confirm="Confirm that all items of {{ $shipment->reference }} have arrived at {{ $shipment->toLocation->name }}?">
                @csrf
                <button type="submit" class="btn btn-primary">Mark as received</button>
            </form>
        @endcan
    </x-slot:actions>

    @error('shipment')
        <div role="alert" class="mb-6 rounded-md bg-clay-50 px-4 py-3 text-sm text-clay-800 ring-1 ring-clay-200">{{ $message }}</div>
    @enderror

    <section class="card" aria-labelledby="shipment-details-heading">
        <h2 id="shipment-details-heading" class="sr-only">Shipment details</h2>
        <dl class="grid grid-cols-1 gap-x-6 gap-y-5 p-4 sm:grid-cols-2 sm:p-6 lg:grid-cols-3">
            <div>
                <dt class="text-sm text-ink-500">Status</dt>
                <dd class="mt-1"><x-shipment-status :status="$shipment->status" /></dd>
            </div>
            <div>
                <dt class="text-sm text-ink-500">From</dt>
                <dd class="mt-1 text-sm">
                    <a href="{{ route('locations.show', $shipment->fromLocation) }}" class="link">{{ $shipment->fromLocation->name }}</a>
                    <span class="block text-xs text-ink-500">{{ $shipment->fromLocation->organization->name }}</span>
                </dd>
            </div>
            <div>
                <dt class="text-sm text-ink-500">To</dt>
                <dd class="mt-1 text-sm">
                    <a href="{{ route('locations.show', $shipment->toLocation) }}" class="link">{{ $shipment->toLocation->name }}</a>
                    <span class="block text-xs text-ink-500">{{ $shipment->toLocation->organization->name }}</span>
                </dd>
            </div>
            <div class="sm:col-span-2 lg:col-span-3">
                <dt class="text-sm text-ink-500">Notes</dt>
                <dd class="mt-1 text-sm whitespace-pre-line text-ink-900">{{ $shipment->notes ?: '—' }}</dd>
            </div>
        </dl>
    </section>

    <section class="card mt-8" aria-labelledby="timeline-heading">
        <div class="border-b border-ink-200 px-4 py-4 sm:px-6">
            <h2 id="timeline-heading" class="text-base font-semibold text-ink-900">Timeline</h2>
        </div>
        <ol class="space-y-4 p-4 text-sm sm:p-6">
            <li>
                <span class="font-medium text-ink-900">Dispatched</span>
                from {{ $shipment->fromLocation->name }} by {{ $shipment->dispatchedBy->name }}
                <time class="block text-ink-500" datetime="{{ $shipment->dispatched_at->toIso8601String() }}">{{ $shipment->dispatched_at->format('d M Y, H:i') }}</time>
            </li>
            @if ($shipment->received_at)
                <li>
                    <span class="font-medium text-brand-700">Received</span>
                    at {{ $shipment->toLocation->name }} by {{ $shipment->receivedBy->name }}
                    <time class="block text-ink-500" datetime="{{ $shipment->received_at->toIso8601String() }}">{{ $shipment->received_at->format('d M Y, H:i') }}</time>
                </li>
            @endif
            @if ($shipment->cancelled_at)
                <li>
                    <span class="font-medium text-ink-700">Cancelled</span>
                    by {{ $shipment->cancelledBy->name }}; stock returned to {{ $shipment->fromLocation->name }}
                    <time class="block text-ink-500" datetime="{{ $shipment->cancelled_at->toIso8601String() }}">{{ $shipment->cancelled_at->format('d M Y, H:i') }}</time>
                    <p class="mt-1 whitespace-pre-line text-ink-700"><span class="font-medium">Reason:</span> {{ $shipment->cancellation_reason }}</p>
                </li>
            @endif
            @if ($shipment->isInTransit())
                <li class="text-brand-700">In transit — awaiting receipt at {{ $shipment->toLocation->name }}.</li>
            @endif
        </ol>
    </section>

    <section class="card mt-8" aria-labelledby="items-heading">
        <div class="border-b border-ink-200 px-4 py-4 sm:px-6">
            <h2 id="items-heading" class="text-base font-semibold text-ink-900">Items</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-ink-200">
                <thead class="bg-ink-50">
                    <tr>
                        <th scope="col" class="table-header">Batch</th>
                        <th scope="col" class="table-header">Product</th>
                        <th scope="col" class="table-header">Batch status</th>
                        <th scope="col" class="table-header text-right">Quantity</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @foreach ($shipment->items as $item)
                        <tr>
                            <td class="table-cell"><a href="{{ route('batches.show', $item->batch) }}" class="link font-mono">{{ $item->batch->batch_number }}</a></td>
                            <td class="table-cell">{{ $item->batch->product->name }}</td>
                            <td class="table-cell"><x-batch-status :status="$item->batch->status" /></td>
                            <td class="table-cell text-right tabular-nums">{{ App\Support\Quantity::format($item->quantity) }} {{ $item->batch->product->unit_of_measure->value }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    @can('cancel', $shipment)
        <section class="mt-8 max-w-3xl rounded-3xl bg-ink-100/70" aria-labelledby="cancel-heading">
            <form method="POST" action="{{ route('shipments.cancellation.store', $shipment) }}" class="p-4 sm:p-6" novalidate
                  data-confirm="Cancel {{ $shipment->reference }} and return all items to {{ $shipment->fromLocation->name }}? This cannot be undone.">
                @csrf
                <h2 id="cancel-heading" class="text-base font-semibold text-ink-900">Cancel shipment</h2>
                <p class="mt-1 text-sm text-ink-500">Returns every item to {{ $shipment->fromLocation->name }}. The dispatch stays in the history.</p>
                <div class="mt-4">
                    <x-form.textarea name="cancellation_reason" label="Reason for cancelling" required rows="2" maxlength="1000"
                                     hint="At least 10 characters." />
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="submit" class="btn btn-danger">Cancel shipment</button>
                </div>
            </form>
        </section>
    @endcan
</x-layouts.app>

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
                <dt class="text-sm text-slate-500">Batch number</dt>
                <dd class="mt-1 font-mono text-sm font-medium text-slate-900">{{ $batch->batch_number }}</dd>
            </div>
            <div>
                <dt class="text-sm text-slate-500">Status</dt>
                <dd class="mt-1"><x-batch-status :status="$batch->status" /></dd>
            </div>
            <div>
                <dt class="text-sm text-slate-500">Product</dt>
                <dd class="mt-1 text-sm">
                    <a href="{{ route('products.show', $batch->product) }}" class="link">{{ $batch->product->name }}</a>
                    <span class="block font-mono text-xs text-slate-500">{{ $batch->product->product_code }}</span>
                </dd>
            </div>
            <div>
                <dt class="text-sm text-slate-500">Manufacturing date</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $batch->manufacturing_date->format('d M Y') }}</dd>
            </div>
            <div>
                <dt class="text-sm text-slate-500">Expiry date</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $batch->expiry_date?->format('d M Y') ?? 'No expiry' }}</dd>
            </div>
            <div>
                <dt class="text-sm text-slate-500">Registered</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $batch->created_at->format('d M Y, H:i') }}</dd>
            </div>
            <div>
                <dt class="text-sm text-slate-500">Initial quantity</dt>
                <dd class="mt-1 text-sm tabular-nums text-slate-900">{{ App\Support\Quantity::format($batch->initial_quantity) }} {{ $unit }}</dd>
            </div>
            <div>
                <dt class="text-sm text-slate-500">Current quantity</dt>
                <dd class="mt-1 text-sm tabular-nums text-slate-900">{{ App\Support\Quantity::format($batch->current_quantity) }} {{ $unit }}</dd>
            </div>
            <div>
                <dt class="text-sm text-slate-500">Last updated</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $batch->updated_at->format('d M Y, H:i') }}</dd>
            </div>
        </dl>
    </section>

    @can('recall', $batch)
        <section class="card mt-8 max-w-3xl border-l-4 border-red-500" aria-labelledby="recall-heading">
            <form method="POST" action="{{ route('batches.recall', $batch) }}" class="p-4 sm:p-6" novalidate
                  data-confirm="Recall batch {{ $batch->batch_number }}? This cannot be undone.">
                @csrf
                <h2 id="recall-heading" class="text-base font-semibold text-slate-900">Recall this batch</h2>
                <p class="mt-1 text-sm text-slate-500">Marks the batch as recalled and freezes its details. This cannot be undone.</p>

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

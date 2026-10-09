<x-layouts.app :title="'Edit batch '.$batch->batch_number">
    <form method="POST" action="{{ route('batches.update', $batch) }}" class="card max-w-3xl" novalidate>
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-6 p-4 sm:grid-cols-2 sm:p-6">
            <div>
                <p class="text-sm font-medium text-ink-700">Product</p>
                <p class="mt-1.5 text-sm text-ink-900">{{ $batch->product->product_code }} — {{ $batch->product->name }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-ink-700">Batch number</p>
                <p class="mt-1.5 font-mono text-sm text-ink-900">{{ $batch->batch_number }}</p>
            </div>
            <p class="text-xs text-ink-500 sm:col-span-2">
                The product and batch number are permanent identifiers. Quantities change only through
                shipments and stock removals, so the movement history always explains the current stock.
            </p>

            <x-form.input type="date" name="manufacturing_date" label="Manufacturing date" required
                          :value="$batch->manufacturing_date->toDateString()"
                          :max="today()->toDateString()" />

            <x-form.input type="date" name="expiry_date" label="Expiry date"
                          :value="$batch->expiry_date?->toDateString()"
                          hint="Leave empty for products that do not expire." />
        </div>

        <div class="flex justify-end gap-3 border-t border-ink-200 px-4 py-4 sm:px-6">
            <a href="{{ route('batches.show', $batch) }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
    </form>
</x-layouts.app>

<x-layouts.app :title="'Edit batch '.$batch->batch_number">
    <form method="POST" action="{{ route('batches.update', $batch) }}" class="card max-w-3xl" novalidate>
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-6 p-4 sm:grid-cols-2 sm:p-6">
            <div>
                <p class="text-sm font-medium text-slate-700">Product</p>
                <p class="mt-1.5 text-sm text-slate-900">{{ $batch->product->product_code }} — {{ $batch->product->name }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-700">Batch number</p>
                <p class="mt-1.5 font-mono text-sm text-slate-900">{{ $batch->batch_number }}</p>
            </div>
            <p class="text-xs text-slate-500 sm:col-span-2">The product and batch number are permanent identifiers and cannot be edited.</p>

            <x-form.input type="date" name="manufacturing_date" label="Manufacturing date" required
                          :value="$batch->manufacturing_date->toDateString()"
                          :max="today()->toDateString()" />

            <x-form.input type="date" name="expiry_date" label="Expiry date"
                          :value="$batch->expiry_date?->toDateString()"
                          hint="Leave empty for products that do not expire." />

            <x-form.input type="number" name="initial_quantity" :label="'Initial quantity ('.$batch->product->unit_of_measure->value.')'" required
                          step="0.001" min="0.001" inputmode="decimal"
                          :value="$batch->initial_quantity" />

            <x-form.input type="number" name="current_quantity" :label="'Current quantity ('.$batch->product->unit_of_measure->value.')'" required
                          step="0.001" min="0" inputmode="decimal"
                          :value="$batch->current_quantity"
                          hint="Manual correction. Stock movements will manage this in a later release." />
        </div>

        <div class="flex justify-end gap-3 border-t border-slate-200 px-4 py-4 sm:px-6">
            <a href="{{ route('batches.show', $batch) }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
    </form>
</x-layouts.app>

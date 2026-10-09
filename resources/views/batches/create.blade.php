<x-layouts.app title="Register batch">
    @if ($products->isEmpty())
        <div class="card max-w-3xl">
            <x-empty-state title="No active products"
                           message="A batch must belong to an active product. Register a product first.">
                <a href="{{ route('products.create') }}" class="btn btn-primary">Register product</a>
            </x-empty-state>
        </div>
    @elseif ($locations->isEmpty())
        <div class="card max-w-3xl">
            <x-empty-state title="No active locations"
                           message="A batch's stock must start at a location, such as the factory where it was produced. Add an organisation and a location first.">
                <a href="{{ route('organizations.index') }}" class="btn btn-primary">Go to supply chain</a>
            </x-empty-state>
        </div>
    @else
        <form method="POST" action="{{ route('batches.store') }}" class="card max-w-3xl" novalidate>
            @csrf

            <div class="grid grid-cols-1 gap-6 p-4 sm:grid-cols-2 sm:p-6">
                <div class="sm:col-span-2">
                    <x-form.select name="product_id" label="Product" required
                                   :value="$batch->product_id"
                                   placeholder="Select a product…"
                                   hint="Only active products are listed."
                                   :options="$products->mapWithKeys(fn ($p) => [$p->id => $p->product_code.' — '.$p->name.' ('.$p->unit_of_measure->value.')'])->all()" />
                </div>

                <div class="sm:col-span-2">
                    <x-form.select name="origin_location_id" label="Production location" required
                                   placeholder="Select where the batch was produced…"
                                   hint="The full initial quantity is placed in stock at this location."
                                   :options="$locations->mapWithKeys(fn ($l) => [$l->id => $l->label().' ('.$l->organization->name.')'])->all()" />
                </div>

                <div class="sm:col-span-2">
                    <x-form.input name="batch_number" label="Batch number" required maxlength="50" autocomplete="off"
                                  class="font-mono uppercase"
                                  hint="Unique lot identifier printed on packaging. It cannot be changed later." />
                </div>

                <x-form.input type="date" name="manufacturing_date" label="Manufacturing date" required
                              :max="today()->toDateString()" />

                <x-form.input type="date" name="expiry_date" label="Expiry date"
                              hint="Leave empty for products that do not expire." />

                <div class="sm:col-span-2">
                    <x-form.input type="number" name="initial_quantity" label="Initial quantity" required
                                  step="0.001" min="0.001" inputmode="decimal"
                                  hint="Up to 3 decimal places, in the product's unit of measure." />
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-200 px-4 py-4 sm:px-6">
                <a href="{{ route('batches.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Register batch</button>
            </div>
        </form>
    @endif
</x-layouts.app>

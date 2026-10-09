<x-layouts.app title="New shipment">
    @if ($locations->count() < 2)
        <div class="card max-w-3xl">
            <x-empty-state title="At least two active locations are needed"
                           message="A shipment moves stock between two locations. Register organisations and their locations first.">
                <a href="{{ route('organizations.index') }}" class="btn btn-primary">Go to supply chain</a>
            </x-empty-state>
        </div>
    @else
        {{-- Step 1: choose the origin (a GET form, so it works without JavaScript). --}}
        <form method="GET" action="{{ route('shipments.create') }}" class="card mb-6 max-w-4xl p-4 sm:p-6">
            <h2 class="text-base font-semibold text-ink-900">1. Ship from</h2>
            <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <label for="from" class="block text-sm font-medium text-ink-700">Origin location</label>
                    <select id="from" name="from" class="form-control mt-1.5" required>
                        <option value="">Select the location the stock leaves from…</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location->id }}" @selected($from?->is($location))>{{ $location->label() }} ({{ $location->organization->name }})</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary">{{ $from ? 'Change origin' : 'Show available stock' }}</button>
            </div>
        </form>

        @if ($from)
            {{-- Step 2: destination and quantities. --}}
            <form method="POST" action="{{ route('shipments.store') }}" class="card max-w-4xl" novalidate
                  data-confirm="Dispatch this shipment? The stock will leave {{ $from->name }} immediately.">
                @csrf
                <input type="hidden" name="from_location_id" value="{{ $from->id }}">

                <div class="space-y-6 p-4 sm:p-6">
                    <h2 class="text-base font-semibold text-ink-900">2. Destination and items</h2>

                    @error('from_location_id')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <x-form.select name="to_location_id" label="Destination" required placeholder="Select the receiving location…"
                                   :options="$locations->reject(fn ($l) => $l->is($from))->mapWithKeys(fn ($l) => [$l->id => $l->label().' ('.$l->organization->name.')'])->all()" />

                    @if ($balances->isEmpty())
                        <div class="rounded-md bg-ink-50 px-4 py-6 text-center text-sm text-ink-600 ring-1 ring-ink-200">
                            There is no shippable stock at {{ $from->name }}. Recalled and expired batches cannot be shipped.
                        </div>
                    @else
                        <fieldset>
                            <legend class="text-sm font-medium text-ink-700">Quantities to ship</legend>
                            <p class="mt-1 text-xs text-ink-500">Leave a row empty to skip that batch.</p>
                            @error('items')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            <div class="mt-3 overflow-x-auto rounded-md ring-1 ring-ink-200">
                                <table class="min-w-full divide-y divide-ink-200">
                                    <thead class="bg-ink-50">
                                        <tr>
                                            <th scope="col" class="table-header">Batch</th>
                                            <th scope="col" class="table-header">Product</th>
                                            <th scope="col" class="table-header">Expires</th>
                                            <th scope="col" class="table-header text-right">Available</th>
                                            <th scope="col" class="table-header w-44">Ship</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-ink-100">
                                        @foreach ($balances as $balance)
                                            @php($field = "items.{$balance->batch_id}.quantity")
                                            @php($inputId = "qty-{$balance->batch_id}")
                                            <tr>
                                                <td class="table-cell font-mono">{{ $balance->batch->batch_number }}</td>
                                                <td class="table-cell">{{ $balance->batch->product->name }}</td>
                                                <td class="table-cell">{{ $balance->batch->expiry_date?->format('d M Y') ?? 'No expiry' }}</td>
                                                <td class="table-cell text-right tabular-nums">{{ App\Support\Quantity::format($balance->quantity) }} {{ $balance->batch->product->unit_of_measure->value }}</td>
                                                <td class="px-4 py-2 align-top">
                                                    <label for="{{ $inputId }}" class="sr-only">Quantity of {{ $balance->batch->batch_number }} to ship</label>
                                                    <input type="number" id="{{ $inputId }}" name="items[{{ $balance->batch_id }}][quantity]"
                                                           value="{{ old($field) }}" step="0.001" min="0" max="{{ $balance->quantity }}" inputmode="decimal"
                                                           @error($field) aria-invalid="true" aria-describedby="{{ $inputId }}-error" @enderror
                                                           class="form-control">
                                                    @error($field)
                                                        <p id="{{ $inputId }}-error" class="mt-1 text-xs text-red-600 whitespace-normal">{{ $message }}</p>
                                                    @enderror
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </fieldset>
                    @endif

                    <x-form.textarea name="notes" label="Notes" rows="2" maxlength="1000"
                                     hint="e.g. vehicle plate number, driver, delivery note number." />
                </div>

                <div class="flex justify-end gap-3 border-t border-ink-200 px-4 py-4 sm:px-6">
                    <a href="{{ route('shipments.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary" @disabled($balances->isEmpty())>Dispatch shipment</button>
                </div>
            </form>
        @endif
    @endif
</x-layouts.app>

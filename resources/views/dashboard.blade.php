<x-layouts.app title="Dashboard">
    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
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
    </dl>

    <section class="card mt-8" aria-labelledby="expiring-heading">
        <div class="border-b border-slate-200 px-4 py-4 sm:px-6">
            <h2 id="expiring-heading" class="text-base font-semibold text-slate-900">Expiry warnings</h2>
            <p class="mt-1 text-sm text-slate-500">Active batches that expire within the next {{ $warningDays }} days, soonest first.</p>
        </div>

        @if ($expiringSoon->isEmpty())
            <x-empty-state title="No batches are approaching expiry"
                           :message="'No active batch expires in the next '.$warningDays.' days.'" />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="table-header">Batch</th>
                            <th scope="col" class="table-header">Product</th>
                            <th scope="col" class="table-header">Expiry date</th>
                            <th scope="col" class="table-header">Remaining</th>
                            <th scope="col" class="table-header text-right">Current quantity</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
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
</x-layouts.app>

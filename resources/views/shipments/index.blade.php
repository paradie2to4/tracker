<x-layouts.app title="Shipments">
    <x-slot:actions>
        @can('create', App\Models\Shipment::class)
            <a href="{{ route('shipments.create') }}" class="btn btn-primary">New shipment</a>
        @endcan
    </x-slot:actions>

    @php($filtered = $filters['q'] !== '' || $filters['status'] || $filters['location'])

    <form method="GET" action="{{ route('shipments.index') }}" role="search" class="card mb-6 grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 lg:grid-cols-[1fr_2fr_1fr_auto] lg:items-end">
        <div>
            <label for="q" class="block text-sm font-medium text-slate-700">Reference</label>
            <input type="search" id="q" name="q" value="{{ $filters['q'] }}" placeholder="e.g. SHP-000042" maxlength="20" class="form-control mt-1.5">
        </div>
        <div>
            <label for="location" class="block text-sm font-medium text-slate-700">Location (origin or destination)</label>
            <select id="location" name="location" class="form-control mt-1.5">
                <option value="">All locations</option>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}" @selected($filters['location'] === $location->id)>{{ $location->label() }} ({{ $location->organization->name }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="status" class="block text-sm font-medium text-slate-700">Status</label>
            <select id="status" name="status" class="form-control mt-1.5">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected($filters['status'] === $status)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary">Filter</button>
            @if ($filtered)
                <a href="{{ route('shipments.index') }}" class="btn btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    <div class="card">
        @if ($shipments->isEmpty())
            @if ($filtered)
                <x-empty-state title="No matching shipments" message="Try a different reference or clear the filters." />
            @else
                <x-empty-state title="No shipments yet" message="Ship stock from one location to another to start building its chain of custody.">
                    <a href="{{ route('shipments.create') }}" class="btn btn-primary">New shipment</a>
                </x-empty-state>
            @endif
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="table-header">Reference</th>
                            <th scope="col" class="table-header">From</th>
                            <th scope="col" class="table-header">To</th>
                            <th scope="col" class="table-header text-right">Batches</th>
                            <th scope="col" class="table-header">Dispatched</th>
                            <th scope="col" class="table-header">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($shipments as $shipment)
                            <tr class="hover:bg-slate-50">
                                <td class="table-cell"><a href="{{ route('shipments.show', $shipment) }}" class="link font-mono">{{ $shipment->reference }}</a></td>
                                <td class="table-cell">{{ $shipment->fromLocation->name }}</td>
                                <td class="table-cell">{{ $shipment->toLocation->name }}</td>
                                <td class="table-cell text-right tabular-nums">{{ $shipment->items_count }}</td>
                                <td class="table-cell">
                                    {{ $shipment->dispatched_at->format('d M Y, H:i') }}
                                    <span class="block text-xs text-slate-500">by {{ $shipment->dispatchedBy->name }}</span>
                                </td>
                                <td class="table-cell"><x-shipment-status :status="$shipment->status" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($shipments->hasPages())
                <div class="border-t border-slate-200 px-4 py-3">{{ $shipments->links() }}</div>
            @endif
        @endif
    </div>
</x-layouts.app>

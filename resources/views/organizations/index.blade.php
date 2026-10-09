<x-layouts.app title="Supply chain">
    <x-slot:actions>
        @can('create', App\Models\Organization::class)
            <a href="{{ route('organizations.create') }}" class="btn btn-primary">Register organisation</a>
        @endcan
    </x-slot:actions>

    @php($filtered = $filters['q'] !== '' || $filters['type'])

    <form method="GET" action="{{ route('organizations.index') }}" role="search" class="card mb-6 grid grid-cols-1 gap-4 p-4 sm:grid-cols-[2fr_1fr_auto] sm:items-end">
        <div>
            <label for="q" class="block text-sm font-medium text-ink-700">Search</label>
            <input type="search" id="q" name="q" value="{{ $filters['q'] }}" placeholder="Organisation name or TIN" maxlength="100" class="form-control mt-1.5">
        </div>
        <div>
            <label for="type" class="block text-sm font-medium text-ink-700">Type</label>
            <select id="type" name="type" class="form-control mt-1.5">
                <option value="">All types</option>
                @foreach ($types as $type)
                    <option value="{{ $type->value }}" @selected($filters['type'] === $type)>{{ $type->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary">Filter</button>
            @if ($filtered)
                <a href="{{ route('organizations.index') }}" class="btn btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    <div class="card">
        @if ($organizations->isEmpty())
            @if ($filtered)
                <x-empty-state title="No matching organisations" message="Try a different search or clear the filters." />
            @else
                <x-empty-state title="No organisations yet"
                               message="Register the manufacturers, distributors and retailers in your supply chain, then add their locations.">
                    @can('create', App\Models\Organization::class)
                        <a href="{{ route('organizations.create') }}" class="btn btn-primary">Register organisation</a>
                    @endcan
                </x-empty-state>
            @endif
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-200">
                    <thead class="bg-ink-50">
                        <tr>
                            <th scope="col" class="table-header">Name</th>
                            <th scope="col" class="table-header">Type</th>
                            <th scope="col" class="table-header">TIN</th>
                            <th scope="col" class="table-header text-right">Locations</th>
                            <th scope="col" class="table-header">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100">
                        @foreach ($organizations as $organization)
                            <tr class="hover:bg-ink-50">
                                <td class="table-cell"><a href="{{ route('organizations.show', $organization) }}" class="link">{{ $organization->name }}</a></td>
                                <td class="table-cell">{{ $organization->type->label() }}</td>
                                <td class="table-cell font-mono">{{ $organization->tin ?? '—' }}</td>
                                <td class="table-cell text-right tabular-nums">{{ $organization->locations_count }}</td>
                                <td class="table-cell"><x-product-status :active="$organization->is_active" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($organizations->hasPages())
                <div class="border-t border-ink-200 px-4 py-3">{{ $organizations->onEachSide(1)->links() }}</div>
            @endif
        @endif
    </div>
</x-layouts.app>

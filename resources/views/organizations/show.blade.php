<x-layouts.app :title="$organization->name">
    <x-slot:actions>
        @can('update', $organization)
            <a href="{{ route('organizations.edit', $organization) }}" class="btn btn-secondary">Edit</a>
        @endcan
        @can('create', App\Models\Location::class)
            <a href="{{ route('organizations.locations.create', $organization) }}" class="btn btn-primary">Add location</a>
        @endcan
    </x-slot:actions>

    <section class="card" aria-labelledby="org-details-heading">
        <h2 id="org-details-heading" class="sr-only">Organisation details</h2>
        <dl class="grid grid-cols-1 gap-x-6 gap-y-5 p-4 sm:grid-cols-2 sm:p-6 lg:grid-cols-3">
            <div>
                <dt class="text-sm text-slate-500">Type</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $organization->type->label() }}</dd>
            </div>
            <div>
                <dt class="text-sm text-slate-500">TIN</dt>
                <dd class="mt-1 font-mono text-sm text-slate-900">{{ $organization->tin ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-sm text-slate-500">Status</dt>
                <dd class="mt-1"><x-product-status :active="$organization->is_active" /></dd>
            </div>
            <div>
                <dt class="text-sm text-slate-500">Contact email</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $organization->contact_email ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-sm text-slate-500">Contact phone</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $organization->contact_phone ?? '—' }}</dd>
            </div>
        </dl>
    </section>

    <section class="card mt-8" aria-labelledby="locations-heading">
        <div class="border-b border-slate-200 px-4 py-4 sm:px-6">
            <h2 id="locations-heading" class="text-base font-semibold text-slate-900">Locations</h2>
            <p class="mt-1 text-sm text-slate-500">Factories, warehouses and shops where this organisation holds stock.</p>
        </div>

        @if ($locations->isEmpty())
            <x-empty-state title="No locations yet" message="Add a location so stock can be produced at, shipped to or held here.">
                @can('create', App\Models\Location::class)
                    <a href="{{ route('organizations.locations.create', $organization) }}" class="btn btn-primary">Add location</a>
                @endcan
            </x-empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="table-header">Code</th>
                            <th scope="col" class="table-header">Name</th>
                            <th scope="col" class="table-header">Type</th>
                            <th scope="col" class="table-header">District</th>
                            <th scope="col" class="table-header">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($locations as $location)
                            <tr class="hover:bg-slate-50">
                                <td class="table-cell"><a href="{{ route('locations.show', $location) }}" class="link font-mono">{{ $location->code }}</a></td>
                                <td class="table-cell">{{ $location->name }}</td>
                                <td class="table-cell">{{ $location->type->label() }}</td>
                                <td class="table-cell">{{ $location->district ?? '—' }}</td>
                                <td class="table-cell"><x-product-status :active="$location->is_active" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-layouts.app>

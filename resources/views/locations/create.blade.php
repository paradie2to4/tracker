<x-layouts.app :title="'Add location to '.$organization->name">
    <form method="POST" action="{{ route('organizations.locations.store', $organization) }}" class="card max-w-3xl" novalidate>
        @csrf

        <div class="p-4 sm:p-6">
            @include('locations.partials.form')
        </div>

        <div class="flex justify-end gap-3 border-t border-slate-200 px-4 py-4 sm:px-6">
            <a href="{{ route('organizations.show', $organization) }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Add location</button>
        </div>
    </form>
</x-layouts.app>

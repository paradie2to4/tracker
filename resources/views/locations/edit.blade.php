<x-layouts.app :title="'Edit '.$location->name">
    <form method="POST" action="{{ route('locations.update', $location) }}" class="card max-w-3xl" novalidate>
        @csrf
        @method('PUT')

        <div class="p-4 sm:p-6">
            @include('locations.partials.form')
        </div>

        <div class="flex justify-end gap-3 border-t border-slate-200 px-4 py-4 sm:px-6">
            <a href="{{ route('locations.show', $location) }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
    </form>
</x-layouts.app>

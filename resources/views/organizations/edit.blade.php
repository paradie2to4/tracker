<x-layouts.app :title="'Edit '.$organization->name">
    <form method="POST" action="{{ route('organizations.update', $organization) }}" class="card max-w-3xl" novalidate>
        @csrf
        @method('PUT')

        <div class="p-4 sm:p-6">
            @include('organizations.partials.form')
        </div>

        <div class="flex justify-end gap-3 border-t border-ink-200 px-4 py-4 sm:px-6">
            <a href="{{ route('organizations.show', $organization) }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
    </form>
</x-layouts.app>

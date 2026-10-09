<x-layouts.app title="Register organisation">
    <form method="POST" action="{{ route('organizations.store') }}" class="card max-w-3xl" novalidate>
        @csrf

        <div class="p-4 sm:p-6">
            @include('organizations.partials.form')
        </div>

        <div class="flex justify-end gap-3 border-t border-ink-200 px-4 py-4 sm:px-6">
            <a href="{{ route('organizations.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Register organisation</button>
        </div>
    </form>
</x-layouts.app>

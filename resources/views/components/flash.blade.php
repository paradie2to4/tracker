@if (session('success'))
    <div role="status" class="mb-6 rounded-md bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-emerald-200">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div role="alert" class="mb-6 rounded-md bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-200">
        {{ session('error') }}
    </div>
@endif

@if ($errors->any())
    <div role="alert" class="mb-6 rounded-md bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-200">
        Please correct the {{ $errors->count() === 1 ? 'error' : $errors->count().' errors' }} below and try again.
    </div>
@endif

@if (session('success'))
    <div role="status" class="mb-6 flex items-center gap-3 rounded-2xl bg-brand-100 px-5 py-3.5 text-sm text-brand-900">
        <svg class="size-5 shrink-0 text-brand-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" /></svg>
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div role="alert" class="mb-6 flex items-center gap-3 rounded-2xl bg-clay-50 px-5 py-3.5 text-sm text-clay-800">
        <svg class="size-5 shrink-0 text-clay-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" /></svg>
        {{ session('error') }}
    </div>
@endif

@if ($errors->any())
    <div role="alert" class="mb-6 flex items-center gap-3 rounded-2xl bg-clay-50 px-5 py-3.5 text-sm text-clay-800">
        <svg class="size-5 shrink-0 text-clay-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" /></svg>
        Please correct the {{ $errors->count() === 1 ? 'error' : $errors->count().' errors' }} below and try again.
    </div>
@endif

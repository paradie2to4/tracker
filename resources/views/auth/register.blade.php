<x-layouts.guest title="Sign up" heading="Create your account" subheading="Explore a working supply chain in under a minute.">
    @if ($errors->any())
        <div role="alert" class="mb-6 rounded-2xl bg-clay-50 px-5 py-3.5 text-sm text-clay-800">
            <ul class="list-inside list-disc space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-5" novalidate>
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-ink-600">Full name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
                   @error('name') aria-invalid="true" @enderror class="form-control mt-1.5 py-2.5">
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-ink-600">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username"
                   @error('email') aria-invalid="true" @enderror class="form-control mt-1.5 py-2.5">
        </div>

        <x-form.password name="password" label="Password" autocomplete="new-password"
                         hint="At least 10 characters, including letters and numbers." />

        <x-form.password name="password_confirmation" label="Confirm password" autocomplete="new-password" />

        <button type="submit" class="btn btn-primary w-full py-3 text-base">Create account</button>

        <p class="text-xs leading-relaxed text-ink-400">
            New accounts get the Staff role: you can explore the demo data, register products and batches, and ship stock.
            Administrative actions such as recalls are reserved for administrators.
        </p>
    </form>

    <p class="mt-8 text-center text-sm text-ink-500">
        Already have an account?
        <a href="{{ route('login') }}" class="link">Log in</a>
    </p>
</x-layouts.guest>

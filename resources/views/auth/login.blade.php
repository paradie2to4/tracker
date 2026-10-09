<x-layouts.guest title="Log in" heading="Log in to your account" subheading="Welcome back. Pick up where your stock left off.">
    @if ($errors->any())
        <div role="alert" class="mb-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-200">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5" novalidate>
        @csrf

        <div>
            <label for="email" class="block text-sm font-semibold text-ink-700">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   @error('email') aria-invalid="true" @enderror class="form-control mt-1.5 py-2.5">
        </div>

        <div>
            <label for="password" class="block text-sm font-semibold text-ink-700">Password</label>
            <input id="password" name="password" type="password" required autocomplete="current-password"
                   @error('password') aria-invalid="true" @enderror class="form-control mt-1.5 py-2.5">
        </div>

        <label class="flex items-center gap-2 text-sm text-ink-600">
            <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="size-4 rounded border-ink-300 text-brand-600 focus:ring-brand-600">
            Keep me signed in on this device
        </label>

        <button type="submit" class="btn btn-primary w-full py-3 text-base">Log in</button>
    </form>

    @if (Route::has('register'))
        <p class="mt-8 text-center text-sm text-ink-500">
            New to {{ config('app.name') }}?
            <a href="{{ route('register') }}" class="link">Create a free account</a>
        </p>
    @endif
</x-layouts.guest>

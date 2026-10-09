<x-layouts.guest title="Log in" heading="Log in to your account" subheading="Welcome back. Pick up where your stock left off.">
    @if ($errors->any())
        <div role="alert" class="mb-6 rounded-2xl bg-clay-50 px-5 py-3.5 text-sm text-clay-800">
            {{ $errors->first() }}
        </div>
    @endif

    @php($demoAccounts = collect(config('productsphere.demo_accounts'))->filter(fn ($account) => filled($account['password'])))
    @if ($demoAccounts->isNotEmpty())
        <div class="mb-6 rounded-3xl bg-brand-100 px-5 py-4 text-sm text-brand-900">
            <p class="font-medium">Demo accounts</p>
            <ul class="mt-2 space-y-1">
                @foreach ($demoAccounts as $role => $account)
                    <li class="flex flex-wrap items-center justify-between gap-2">
                        <span class="font-mono text-xs">{{ $account['email'] }}</span>
                        <span class="chip bg-surface py-0.5 text-xs">{{ ucfirst($role) }}</span>
                    </li>
                @endforeach
            </ul>
            <p class="mt-2 text-xs text-brand-800/70">Ask the presenter for the password, or create your own free account.</p>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5" novalidate>
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-ink-600">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   @error('email') aria-invalid="true" @enderror class="form-control mt-1.5 py-2.5">
        </div>

        <x-form.password name="password" label="Password" autocomplete="current-password" />

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

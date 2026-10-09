<x-layouts.guest title="Log in">
    <div class="card p-6 sm:p-8">
        <h1 class="text-lg font-semibold text-slate-900">Log in to your account</h1>

        @if ($errors->any())
            <div role="alert" class="mt-4 rounded-md bg-red-50 px-3 py-2 text-sm text-red-800 ring-1 ring-red-200">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5" novalidate>
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-slate-700">Email address</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       @error('email') aria-invalid="true" @enderror class="form-control mt-1.5">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                <input id="password" name="password" type="password" required autocomplete="current-password"
                       @error('password') aria-invalid="true" @enderror class="form-control mt-1.5">
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="size-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-600">
                Keep me signed in on this device
            </label>

            <button type="submit" class="btn btn-primary w-full">Log in</button>
        </form>
    </div>

    <p class="mt-6 text-center text-xs text-slate-500">
        Accounts are created by an administrator. Contact your administrator if you need access.
    </p>
</x-layouts.guest>

<x-layouts.guest title="Sign up">
    <div class="card p-6 sm:p-8">
        <h1 class="text-lg font-semibold text-slate-900">Create your account</h1>

        @if ($errors->any())
            <div role="alert" class="mt-4 rounded-md bg-red-50 px-3 py-2 text-sm text-red-800 ring-1 ring-red-200">
                <ul class="list-inside list-disc space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-5" novalidate>
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-slate-700">Full name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
                       @error('name') aria-invalid="true" @enderror class="form-control mt-1.5">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-slate-700">Email address</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username"
                       @error('email') aria-invalid="true" @enderror class="form-control mt-1.5">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                <input id="password" name="password" type="password" required autocomplete="new-password" aria-describedby="password-hint"
                       @error('password') aria-invalid="true" @enderror class="form-control mt-1.5">
                <p id="password-hint" class="mt-1 text-xs text-slate-500">At least 10 characters, including letters and numbers.</p>
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                       class="form-control mt-1.5">
            </div>

            <button type="submit" class="btn btn-primary w-full">Sign up</button>
        </form>
    </div>

    <p class="mt-6 text-center text-sm text-slate-600">
        Already have an account?
        <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">Log in</a>
    </p>
</x-layouts.guest>

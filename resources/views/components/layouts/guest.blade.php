@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name') }}</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="flex min-h-full items-center justify-center px-4 py-12 font-sans text-slate-800 antialiased">
        <main class="w-full max-w-sm">
            <div class="mb-8 text-center">
                <p class="text-2xl font-semibold tracking-tight text-slate-900">{{ config('app.name') }}</p>
                <p class="mt-1 text-sm text-slate-500">Product traceability management</p>
            </div>
            {{ $slot }}
        </main>
    </body>
</html>

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
    <body class="h-full font-sans text-slate-800 antialiased">
        <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 focus:rounded focus:bg-white focus:px-3 focus:py-2 focus:shadow">
            Skip to main content
        </a>

        <div class="min-h-full md:flex">
            <aside class="bg-slate-900 text-slate-100 md:flex md:w-64 md:shrink-0 md:flex-col">
                <div class="flex items-center justify-between gap-4 px-4 py-4 md:block md:px-5 md:py-6">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-lg font-semibold tracking-tight text-white">
                        <svg class="size-6 text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        </svg>
                        {{ config('app.name') }}
                    </a>
                </div>

                <nav aria-label="Main" class="px-2 pb-3 md:flex-1 md:px-3 md:pb-0">
                    <ul class="flex gap-1 overflow-x-auto md:flex-col">
                        <li><x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dashboard</x-nav-link></li>
                        <li><x-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')">Products</x-nav-link></li>
                        <li><x-nav-link :href="route('batches.index')" :active="request()->routeIs('batches.*')">Batches</x-nav-link></li>
                    </ul>
                </nav>

                <div class="flex items-center justify-between gap-3 border-t border-slate-800 px-4 py-3 md:px-5 md:py-4">
                    <div class="min-w-0 text-sm">
                        <p class="truncate font-medium text-white">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-slate-400">{{ auth()->user()->role->label() }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-md px-2.5 py-1.5 text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white focus-visible:outline-2 focus-visible:outline-indigo-400">
                            Log out
                        </button>
                    </form>
                </div>
            </aside>

            <div class="min-w-0 flex-1">
                <header class="border-b border-slate-200 bg-white">
                    <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-4 sm:px-6 lg:px-8">
                        <h1 class="text-xl font-semibold tracking-tight text-slate-900">{{ $title }}</h1>
                        @isset($actions)
                            <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
                        @endisset
                    </div>
                </header>

                <main id="main" class="px-4 py-6 sm:px-6 lg:px-8">
                    <x-flash />
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>

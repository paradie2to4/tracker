@props(['title' => null])

@php
    $navigation = [
        ['Dashboard', 'dashboard', ['dashboard'], 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z'],
        ['Products', 'products.index', ['products.*'], 'M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9'],
        ['Batches', 'batches.index', ['batches.*'], 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z'],
        ['Shipments', 'shipments.index', ['shipments.*'], 'M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12'],
        ['Supply chain', 'organizations.index', ['organizations.*', 'locations.*'], 'M15 10.5a3 3 0 11-6 0 3 3 0 016 0z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z'],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name') }}</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full font-sans text-ink-800 antialiased">
        <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 focus:rounded focus:bg-white focus:px-3 focus:py-2 focus:shadow">
            Skip to main content
        </a>

        <div class="min-h-full md:flex">
            <aside class="relative isolate overflow-hidden bg-ink-950 text-ink-100 md:sticky md:top-0 md:flex md:h-screen md:w-64 md:shrink-0 md:flex-col">
                <div class="absolute -top-24 -left-24 -z-10 size-72 rounded-full bg-brand-600/25 blur-3xl" aria-hidden="true"></div>

                <div class="px-4 py-4 md:px-5 md:py-6">
                    <a href="{{ route('dashboard') }}" aria-label="{{ config('app.name') }} dashboard"><x-logo dark /></a>
                </div>

                <nav aria-label="Main" class="px-2 pb-3 md:flex-1 md:overflow-y-auto md:px-3 md:pb-0">
                    <p class="hidden px-3 pb-2 text-[11px] font-bold tracking-[0.16em] text-ink-400 uppercase md:block">Workspace</p>
                    <ul class="flex gap-1 overflow-x-auto md:flex-col">
                        @foreach ($navigation as [$label, $route, $patterns, $icon])
                            <li><x-nav-link :href="route($route)" :active="request()->routeIs(...$patterns)" :icon="$icon">{{ $label }}</x-nav-link></li>
                        @endforeach
                        @can('viewAny', App\Models\AuditLog::class)
                            <li>
                                <x-nav-link :href="route('audit.index')" :active="request()->routeIs('audit.*')"
                                            icon="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z">Audit log</x-nav-link>
                            </li>
                        @endcan
                    </ul>
                </nav>

                <div class="flex items-center justify-between gap-3 border-t border-white/10 px-4 py-3 md:px-5 md:py-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-brand-500 to-sky-500 text-sm font-bold text-white" aria-hidden="true">
                            {{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}
                        </span>
                        <div class="min-w-0 text-sm">
                            <p class="truncate font-semibold text-white">{{ auth()->user()->name }}</p>
                            <p class="truncate text-xs {{ auth()->user()->isAdmin() ? 'text-sun-400' : 'text-ink-400' }}">{{ auth()->user()->role->label() }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                        @csrf
                        <button type="submit" class="rounded-lg px-2.5 py-1.5 text-sm font-medium whitespace-nowrap text-ink-300 hover:bg-white/10 hover:text-white focus-visible:outline-2 focus-visible:outline-sky-400">
                            Log out
                        </button>
                    </form>
                </div>
            </aside>

            <div class="min-w-0 flex-1 bg-canvas">
                <header class="sticky top-0 z-20 border-b border-ink-100 bg-white/85 backdrop-blur">
                    <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-4 sm:px-6 lg:px-8">
                        <h1 class="text-xl font-extrabold tracking-tight text-ink-900">{{ $title }}</h1>
                        @isset($actions)
                            <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
                        @endisset
                    </div>
                </header>

                <main id="main" class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                    <x-flash />
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>

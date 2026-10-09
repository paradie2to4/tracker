@props(['title' => null, 'heading' => null, 'subheading' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name') }}</title>
        @include('partials.head-icons')
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full bg-canvas font-sans text-ink-800 antialiased">
        <div class="flex min-h-full gap-4 p-4">
            {{-- Brand panel (large screens). --}}
            <aside class="panel relative isolate hidden w-[46%] max-w-2xl flex-col justify-between overflow-hidden p-12 lg:flex">
                <x-deco-lines class="-top-6 -right-10 -z-10 w-[28rem]" />

                <a href="{{ route('home') }}" aria-label="{{ config('app.name') }} home"><x-logo size="lg" /></a>

                <div>
                    {{-- A small, quiet product preview. --}}
                    <div class="card max-w-sm p-6">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-ink-500">Batch UM-MAIZE-2026-031</p>
                            <span class="rounded-full bg-brand-100 px-2.5 py-0.5 text-xs text-brand-800">Active</span>
                        </div>
                        <p class="mt-3 text-3xl font-medium tracking-tight text-ink-900 tabular-nums">710 <span class="text-base font-normal text-ink-400">bags in the chain</span></p>
                        <div class="mt-5 flex items-center gap-2 text-xs text-ink-500">
                            @foreach (['Masoro mill', 'Kigali warehouse', 'Musanze shop'] as $i => $stop)
                                @if ($i > 0)
                                    <span class="h-px flex-1 bg-ink-200" aria-hidden="true"></span>
                                @endif
                                <span class="flex items-center gap-1.5 whitespace-nowrap">
                                    <span class="size-2 rounded-full {{ $i === 2 ? 'bg-gold-400' : 'bg-brand-700' }}" aria-hidden="true"></span>
                                    {{ $stop }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <p class="mt-10 max-w-md text-3xl leading-tight font-medium tracking-tight text-brand-950">
                        Every batch, every location, every movement.
                    </p>
                    <p class="mt-4 max-w-md text-brand-800/80">
                        See where your stock is right now, and the full history of how it got there.
                    </p>
                </div>

                <p class="text-sm text-brand-800/70">Product traceability for Rwandan supply chains</p>
            </aside>

            {{-- Form panel. --}}
            <main class="flex flex-1 flex-col justify-center px-2 py-10 sm:px-6 lg:px-16">
                <div class="mx-auto w-full max-w-md">
                    <a href="{{ route('home') }}" class="mb-10 inline-block lg:hidden" aria-label="{{ config('app.name') }} home"><x-logo /></a>

                    @if ($heading)
                        <h1 class="text-3xl font-medium tracking-tight text-ink-900">{{ $heading }}</h1>
                    @endif
                    @if ($subheading)
                        <p class="mt-2 text-ink-500">{{ $subheading }}</p>
                    @endif

                    <div class="mt-8">
                        {{ $slot }}
                    </div>
                </div>
            </main>
        </div>
    </body>
</html>

@props(['title' => null, 'heading' => null, 'subheading' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name') }}</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full bg-white font-sans text-ink-800 antialiased">
        <div class="flex min-h-full">
            {{-- Brand panel (large screens). --}}
            <aside class="relative isolate hidden w-[44%] max-w-2xl overflow-hidden bg-ink-950 text-white lg:flex lg:flex-col lg:justify-between lg:p-12">
                <div class="bg-grid-dark absolute inset-0 -z-10" aria-hidden="true"></div>
                <div class="absolute -top-32 -left-32 -z-10 size-[28rem] rounded-full bg-brand-600/35 blur-3xl" aria-hidden="true"></div>
                <div class="absolute -right-24 bottom-0 -z-10 size-80 rounded-full bg-sky-500/20 blur-3xl" aria-hidden="true"></div>

                <a href="{{ route('home') }}" aria-label="{{ config('app.name') }} home"><x-logo dark size="lg" /></a>

                <div>
                    <p class="text-3xl leading-tight font-extrabold tracking-tight text-balance">
                        Every batch. Every location.
                        <span class="text-sun-400">Every movement.</span>
                    </p>
                    <ul class="mt-8 space-y-4 text-ink-200">
                        @foreach ([
                            'See where your stock is right now, across every warehouse and shop.',
                            'Ship, receive and recall with a ledger that cannot be rewritten.',
                            'Answer “where did this batch go?” in seconds, not days.',
                        ] as $point)
                            <li class="flex gap-3">
                                <span class="mt-2 size-1.5 shrink-0 rounded-full bg-sky-400" aria-hidden="true"></span>
                                {{ $point }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <p class="text-sm text-ink-400">Product traceability for Rwandan supply chains.</p>
            </aside>

            {{-- Form panel. --}}
            <main class="flex flex-1 flex-col justify-center bg-canvas px-4 py-12 sm:px-6 lg:px-16">
                <div class="mx-auto w-full max-w-md">
                    <a href="{{ route('home') }}" class="mb-10 inline-block lg:hidden" aria-label="{{ config('app.name') }} home"><x-logo /></a>

                    @if ($heading)
                        <h1 class="text-2xl font-extrabold tracking-tight text-ink-900 sm:text-3xl">{{ $heading }}</h1>
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

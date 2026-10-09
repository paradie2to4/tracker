<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name') }} · Product traceability for Rwandan supply chains</title>
        <meta name="description" content="Register products and batches, ship them between locations, and trace any batch from factory to shelf with a tamper-proof stock ledger.">
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-canvas font-sans text-ink-800 antialiased">
        <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-3 focus:left-3 focus:z-50 focus:rounded-full focus:bg-surface focus:px-4 focus:py-2 focus:shadow">
            Skip to main content
        </a>

        <header class="relative isolate overflow-hidden">
            <x-deco-lines class="-top-24 right-[-6rem] -z-10 w-[44rem]" />
            <x-deco-lines class="top-[22rem] -left-40 -z-10 w-[30rem] rotate-180 opacity-70" />

            <nav class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-6 sm:px-6 lg:px-8" aria-label="Main">
                <a href="{{ route('home') }}" aria-label="{{ config('app.name') }} home"><x-logo /></a>
                <div class="hidden items-center gap-1 rounded-full bg-surface/80 p-1 text-sm text-ink-600 shadow-[0_1px_2px_rgb(60_50_30/0.05)] md:flex">
                    <a href="#features" class="rounded-full px-4 py-2 hover:bg-ink-100 hover:text-ink-900">Features</a>
                    <a href="#how-it-works" class="rounded-full px-4 py-2 hover:bg-ink-100 hover:text-ink-900">How it works</a>
                    <a href="#who-its-for" class="rounded-full px-4 py-2 hover:bg-ink-100 hover:text-ink-900">Who it's for</a>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('login') }}" class="btn btn-ghost">Log in</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-primary hidden sm:inline-flex">Get started</a>
                    @endif
                </div>
            </nav>

            <div class="mx-auto grid max-w-7xl items-center gap-14 px-4 pt-10 pb-20 sm:px-6 lg:grid-cols-[1.1fr_1fr] lg:px-8 lg:pt-16 lg:pb-28">
                <div>
                    <p class="chip gap-2 bg-surface text-ink-600">
                        <span class="size-1.5 rounded-full bg-gold-400" aria-hidden="true"></span>
                        Built for Rwanda's supply chains
                    </p>
                    <h1 class="mt-7 text-4xl leading-[1.08] font-medium tracking-tight text-ink-900 text-balance sm:text-5xl lg:text-6xl">
                        Know where every batch is,
                        <span class="text-brand-600">from factory to shelf.</span>
                    </h1>
                    <p class="mt-6 max-w-xl text-lg leading-relaxed text-ink-500">
                        ProductSphere follows your products as they move between manufacturers, warehouses and shops.
                        Every shipment, sale and recall is kept in a ledger that can't be rewritten, so
                        “where is it, and how did it get there?” takes seconds to answer.
                    </p>
                    <div class="mt-9 flex flex-wrap items-center gap-3">
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-primary px-7 py-3.5 text-base">
                                Explore the live demo
                                <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.64l-3.97-3.97a.75.75 0 1 1 1.06-1.06l5.25 5.25a.75.75 0 0 1 0 1.06l-5.25 5.25a.75.75 0 1 1-1.06-1.06l3.97-3.97H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" /></svg>
                            </a>
                        @endif
                        <a href="#how-it-works" class="btn btn-secondary px-7 py-3.5 text-base">See how it works</a>
                    </div>
                    <p class="mt-5 text-sm text-ink-400">Free account · no setup · sample data from a Rwandan supply chain included</p>
                </div>

                {{-- Product preview, composed like the app itself: white cards on a sage panel. --}}
                <div class="panel p-4 sm:p-5" aria-label="Example: one batch's stock and journey">
                    <div class="card p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm text-ink-400">UM-MAIZE-2026-031</p>
                                <p class="mt-1 text-lg font-medium text-ink-900">Fortified maize flour · 25 kg</p>
                            </div>
                            <span class="rounded-full bg-brand-100 px-3 py-1 text-xs text-brand-800">Active</span>
                        </div>

                        <ol class="mt-6 space-y-4">
                            @foreach ([
                                ['Masoro mill', 'Produced', '1,200', false],
                                ['Kigali central warehouse', 'Received', '250', false],
                                ['Musanze town shop', 'Received', '30', false],
                                ['On the road to Nyamata', 'In transit', '100', true],
                            ] as [$place, $event, $qty, $current])
                                <li class="flex items-center gap-4">
                                    <span class="flex size-9 shrink-0 items-center justify-center rounded-full {{ $current ? 'bg-brand-800 text-gold-300' : 'bg-brand-100 text-brand-700' }}" aria-hidden="true">
                                        <svg class="size-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="m9.69 18.933.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 0 0 .281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 1 0 3 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 0 0 2.273 1.765 11.842 11.842 0 0 0 .976.544l.062.029.018.008.006.003ZM10 11.25a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z" clip-rule="evenodd" /></svg>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm text-ink-900">{{ $place }}</p>
                                        <p class="text-xs text-ink-400">{{ $event }}</p>
                                    </div>
                                    <p class="text-sm font-medium text-ink-700 tabular-nums">{{ $qty }}</p>
                                </li>
                            @endforeach
                        </ol>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div class="card p-5">
                            <p class="text-sm text-ink-500">Still in the chain</p>
                            <p class="mt-2 text-3xl font-medium text-ink-900 tabular-nums">710</p>
                        </div>
                        <div class="card p-5">
                            <p class="text-sm text-ink-500">Days to expiry</p>
                            <p class="mt-2 text-3xl font-medium text-ink-900 tabular-nums">290</p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main id="main">
            {{-- Live figures from the platform. --}}
            <section aria-label="Platform figures" class="px-4 sm:px-6 lg:px-8">
                <dl class="mx-auto grid max-w-6xl grid-cols-2 gap-4 md:grid-cols-4">
                    @foreach ([
                        ['Batches tracked', $stats['batches']],
                        ['Supply-chain locations', $stats['locations']],
                        ['Shipments recorded', $stats['shipments']],
                        ['Ledger entries', $stats['movements']],
                    ] as [$label, $value])
                        <div class="card px-6 py-6">
                            <dt class="text-sm text-ink-500">{{ $label }}</dt>
                            <dd class="mt-2 text-4xl font-medium tracking-tight text-ink-900 tabular-nums">{{ number_format($value) }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>

            {{-- Features --}}
            <section id="features" class="mx-auto max-w-7xl scroll-mt-8 px-4 py-28 sm:px-6 lg:px-8" aria-labelledby="features-heading">
                <div class="max-w-2xl">
                    <p class="eyebrow">Features</p>
                    <h2 id="features-heading" class="mt-3 text-3xl font-medium tracking-tight text-ink-900 sm:text-4xl">Everything you need to trust your stock data</h2>
                    <p class="mt-4 text-lg text-ink-500">From the moment a batch is produced to the moment it is sold, recalled or disposed of.</p>
                </div>

                <div class="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ([
                        ['Products & batches', 'Register products with unique codes, then record each manufacturing batch with its dates and exact quantities.', 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z'],
                        ['Shipments between locations', 'Dispatch stock from a factory to a warehouse or shop. It is tracked in transit until it is received or cancelled.', 'M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12'],
                        ['Expiry awareness', 'Expired and soon-to-expire batches are flagged automatically, so stock is moved or disposed of in time.', 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['Recalls', 'Recall a batch with a recorded reason. It immediately stops being shippable anywhere in the chain.', 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z'],
                        ['A ledger that cannot be rewritten', 'Every quantity change is an append-only movement. The database itself refuses edits and deletions.', 'M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z'],
                        ['Full audit trail', 'See who changed what, when and from where, for accountability with regulators and partners.', 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z'],
                    ] as [$title, $body, $icon])
                        <article class="card p-7">
                            <span class="inline-flex size-12 items-center justify-center rounded-2xl bg-brand-100 text-brand-700">
                                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" /></svg>
                            </span>
                            <h3 class="mt-6 text-lg font-medium text-ink-900">{{ $title }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-ink-500">{{ $body }}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            {{-- How it works --}}
            <section id="how-it-works" class="scroll-mt-8 px-4 sm:px-6 lg:px-8" aria-labelledby="how-heading">
                <div class="panel relative isolate mx-auto max-w-7xl overflow-hidden px-6 py-20 sm:px-12">
                    <x-deco-lines class="-right-20 -bottom-24 -z-10 w-[32rem]" />
                    <div class="mx-auto max-w-2xl text-center">
                        <p class="eyebrow">How it works</p>
                        <h2 id="how-heading" class="mt-3 text-3xl font-medium tracking-tight text-brand-950 sm:text-4xl">Three steps to a traceable supply chain</h2>
                    </div>

                    <ol class="mt-14 grid gap-5 lg:grid-cols-3">
                        @foreach ([
                            ['Register', 'Add your organisations and locations, your products, and each batch where it is produced.'],
                            ['Move', 'Ship batches between locations. Receiving confirms delivery; sales and losses are recorded as removals.'],
                            ['Trace', 'Open any batch to see where its stock is right now and every step it took to get there.'],
                        ] as $i => [$title, $body])
                            <li class="card p-8">
                                <span class="flex size-11 items-center justify-center rounded-full bg-brand-800 text-base font-medium text-gold-300">{{ $i + 1 }}</span>
                                <h3 class="mt-6 text-xl font-medium text-ink-900">{{ $title }}</h3>
                                <p class="mt-2 leading-relaxed text-ink-500">{{ $body }}</p>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>

            {{-- Who it's for --}}
            <section id="who-its-for" class="mx-auto max-w-7xl scroll-mt-8 px-4 py-28 sm:px-6 lg:px-8" aria-labelledby="who-heading">
                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <div>
                        <p class="eyebrow">Who it's for</p>
                        <h2 id="who-heading" class="mt-3 text-3xl font-medium tracking-tight text-ink-900 sm:text-4xl">Made for the way goods move in Rwanda</h2>
                        <p class="mt-4 text-lg text-ink-500">
                            Locations are organised by Rwanda's 30 districts and organisations by RRA TIN.
                            Quantities are exact to three decimals, whether you count tablets, bags or tonnes.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-2">
                            @foreach (['Food & beverage producers', 'Pharmaceutical distributors', 'Agricultural cooperatives', 'Wholesalers', 'Pharmacies & shops'] as $who)
                                <span class="chip">{{ $who }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div class="card relative isolate overflow-hidden p-8 sm:p-10">
                        <x-deco-lines class="-top-10 -right-16 -z-10 w-80" />
                        <p class="text-sm text-ink-500">When a recall happens</p>
                        <p class="mt-4 text-2xl leading-snug font-medium text-ink-900">
                            “Which shops still hold batch <span class="whitespace-nowrap text-brand-600">UZ-PCM-2026-0098</span>?”
                        </p>
                        <p class="mt-4 text-ink-500">
                            Without traceability, that question takes days of phone calls. In ProductSphere, the batch page
                            shows every location holding stock, and every shipment still on the road, as soon as you open it.
                        </p>
                        <div class="mt-8 grid grid-cols-2 gap-4">
                            <div class="rounded-3xl bg-brand-100 p-5">
                                <p class="text-3xl font-medium text-brand-900">Seconds</p>
                                <p class="mt-1 text-sm text-brand-800/70">to locate affected stock</p>
                            </div>
                            <div class="rounded-3xl bg-ink-100 p-5">
                                <p class="text-3xl font-medium text-ink-900">Every</p>
                                <p class="mt-1 text-sm text-ink-500">movement on record</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Call to action --}}
            <section class="px-4 pb-24 sm:px-6 lg:px-8" aria-labelledby="cta-heading">
                <div class="relative isolate mx-auto max-w-5xl overflow-hidden rounded-4xl bg-brand-800 px-6 py-16 text-center sm:px-12">
                    <x-deco-lines class="-top-10 -left-16 -z-10 w-[30rem] opacity-60" />
                    <h2 id="cta-heading" class="text-3xl font-medium tracking-tight text-brand-50 sm:text-4xl">See it with real supply-chain data</h2>
                    <p class="mx-auto mt-4 max-w-xl text-lg text-brand-200">
                        Create a free account and explore a working supply chain: mills, warehouses, pharmacies and shops across Rwanda.
                    </p>
                    <div class="mt-8 flex flex-wrap justify-center gap-3">
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn bg-brand-50 px-7 py-3.5 text-base text-brand-900 hover:bg-white focus-visible:outline-brand-50">Create free account</a>
                        @endif
                        <a href="{{ route('login') }}" class="btn px-7 py-3.5 text-base text-brand-100 ring-1 ring-brand-500 ring-inset hover:bg-brand-700 focus-visible:outline-brand-50">I already have an account</a>
                    </div>
                </div>
            </section>
        </main>

        <footer>
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 py-10 text-sm text-ink-400 sm:flex-row sm:px-6 lg:px-8">
                <x-logo />
                <p>Product traceability for Rwandan supply chains</p>
            </div>
        </footer>
    </body>
</html>

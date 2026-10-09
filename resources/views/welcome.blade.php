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
    <body class="bg-white font-sans text-ink-800 antialiased">
        <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-3 focus:left-3 focus:z-50 focus:rounded-md focus:bg-white focus:px-3 focus:py-2 focus:text-ink-900 focus:shadow">
            Skip to main content
        </a>

        {{-- ================= HERO ================= --}}
        <header class="relative isolate overflow-hidden bg-ink-950 text-white">
            <div class="bg-grid-dark absolute inset-0 -z-10 [mask-image:radial-gradient(ellipse_at_top,black_30%,transparent_75%)]" aria-hidden="true"></div>
            <div class="absolute -top-40 left-1/2 -z-10 h-[36rem] w-[60rem] -translate-x-1/2 rounded-full bg-brand-600/30 blur-3xl" aria-hidden="true"></div>
            <div class="absolute top-64 -right-32 -z-10 h-80 w-80 rounded-full bg-sky-500/20 blur-3xl" aria-hidden="true"></div>

            <nav class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-5 sm:px-6 lg:px-8" aria-label="Main">
                <a href="{{ route('home') }}" aria-label="{{ config('app.name') }} home"><x-logo dark /></a>
                <div class="hidden items-center gap-8 text-sm font-medium text-ink-200 md:flex">
                    <a href="#features" class="hover:text-white">Features</a>
                    <a href="#how-it-works" class="hover:text-white">How it works</a>
                    <a href="#who-its-for" class="hover:text-white">Who it's for</a>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('login') }}" class="btn btn-ghost-dark">Log in</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-accent hidden sm:inline-flex">Get started</a>
                    @endif
                </div>
            </nav>

            <div class="mx-auto grid max-w-7xl items-center gap-14 px-4 pt-12 pb-24 sm:px-6 lg:grid-cols-[1.05fr_1fr] lg:px-8 lg:pt-20 lg:pb-32">
                <div>
                    <p class="inline-flex items-center gap-2 rounded-full bg-white/5 px-3 py-1 text-xs font-semibold text-sky-300 ring-1 ring-white/10">
                        <span class="size-1.5 rounded-full bg-sun-400"></span>
                        Built for Rwanda's supply chains
                    </p>
                    <h1 class="mt-6 text-4xl font-extrabold tracking-tight text-balance sm:text-5xl lg:text-6xl">
                        Know where every batch is.
                        <span class="bg-gradient-to-r from-sky-300 via-brand-300 to-sun-300 bg-clip-text text-transparent">From factory to shelf.</span>
                    </h1>
                    <p class="mt-6 max-w-xl text-lg leading-relaxed text-ink-200">
                        ProductSphere tracks products and batches as they move between manufacturers, warehouses and shops.
                        Every shipment, sale and recall is recorded in a tamper-proof ledger, so you can answer
                        <em class="font-semibold text-white not-italic">“where is it, and how did it get there?”</em> in seconds.
                    </p>
                    <div class="mt-9 flex flex-wrap items-center gap-3">
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-accent px-6 py-3 text-base">
                                Explore the live demo
                                <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.64l-3.97-3.97a.75.75 0 1 1 1.06-1.06l5.25 5.25a.75.75 0 0 1 0 1.06l-5.25 5.25a.75.75 0 1 1-1.06-1.06l3.97-3.97H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" /></svg>
                            </a>
                        @endif
                        <a href="#how-it-works" class="btn btn-ghost-dark px-6 py-3 text-base">See how it works</a>
                    </div>
                    <p class="mt-4 text-sm text-ink-300">Free account · no setup · sample data from a Rwandan supply chain included.</p>
                </div>

                {{-- Product preview: a batch's chain of custody. --}}
                <div class="relative" aria-label="Example: the chain of custody of one batch">
                    <div class="absolute -inset-4 -z-10 rounded-[2rem] bg-gradient-to-br from-brand-500/30 via-sky-500/10 to-sun-400/20 blur-2xl" aria-hidden="true"></div>
                    <div class="rounded-2xl bg-white p-5 text-ink-800 shadow-2xl shadow-ink-950/50 ring-1 ring-white/10 sm:p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-mono text-xs font-semibold text-ink-400">UM-MAIZE-2026-031</p>
                                <p class="mt-1 text-lg font-bold text-ink-900">Fortified maize flour · 25 kg</p>
                                <p class="text-sm text-ink-500">Umurage Mills Ltd</p>
                            </div>
                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-600/20">Active</span>
                        </div>

                        <div class="mt-5 grid grid-cols-3 gap-2 text-center">
                            <div class="rounded-lg bg-ink-50 px-2 py-2.5">
                                <p class="text-lg font-extrabold text-ink-900 tabular-nums">1,200</p>
                                <p class="text-[11px] font-medium text-ink-500">produced</p>
                            </div>
                            <div class="rounded-lg bg-sky-50 px-2 py-2.5">
                                <p class="text-lg font-extrabold text-sky-700 tabular-nums">300</p>
                                <p class="text-[11px] font-medium text-ink-500">in transit</p>
                            </div>
                            <div class="rounded-lg bg-sun-200/50 px-2 py-2.5">
                                <p class="text-lg font-extrabold text-sun-600 tabular-nums">142 d</p>
                                <p class="text-[11px] font-medium text-ink-500">to expiry</p>
                            </div>
                        </div>

                        <ol class="relative mt-6 space-y-5 border-l-2 border-dashed border-ink-100 pl-6">
                            @foreach ([
                                ['Produced', 'Masoro mill · Gasabo', '+1,200', 'bg-brand-600', 'text-emerald-600'],
                                ['Shipped SHP-000128', 'To Kigali central warehouse', '−600', 'bg-brand-600', 'text-ink-500'],
                                ['Received', 'Kigali central warehouse · Nyarugenge', '+600', 'bg-brand-600', 'text-emerald-600'],
                                ['Shipped SHP-000131', 'To Musanze Fresh Market · in transit', '−300', 'bg-sun-400 ring-4 ring-sun-200', 'text-ink-500'],
                            ] as [$event, $place, $qty, $dot, $qtyColour])
                                <li class="relative">
                                    <span class="absolute top-1 -left-[1.95rem] size-3 rounded-full {{ $dot }}" aria-hidden="true"></span>
                                    <div class="flex items-baseline justify-between gap-3">
                                        <p class="text-sm font-semibold text-ink-900">{{ $event }}</p>
                                        <p class="text-sm font-bold tabular-nums {{ $qtyColour }}">{{ $qty }}</p>
                                    </div>
                                    <p class="text-xs text-ink-500">{{ $place }}</p>
                                </li>
                            @endforeach
                        </ol>

                        <div class="mt-6 flex items-center gap-2 rounded-lg bg-ink-950 px-3 py-2.5 text-xs text-ink-200">
                            <svg class="size-4 shrink-0 text-sun-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd" /></svg>
                            Ledger entries are append-only: history can be added to, never rewritten.
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main id="main">
            {{-- ================= LIVE NUMBERS ================= --}}
            <section aria-label="Platform figures" class="relative z-10 -mt-12">
                <dl class="mx-auto grid max-w-5xl grid-cols-2 gap-px overflow-hidden rounded-2xl bg-ink-100 shadow-xl shadow-ink-900/10 ring-1 ring-ink-100 md:grid-cols-4">
                    @foreach ([
                        ['Batches tracked', $stats['batches']],
                        ['Supply-chain locations', $stats['locations']],
                        ['Shipments recorded', $stats['shipments']],
                        ['Ledger entries', $stats['movements']],
                    ] as [$label, $value])
                        <div class="bg-white px-6 py-6 text-center">
                            <dd class="text-3xl font-extrabold tracking-tight text-ink-900 tabular-nums">{{ number_format($value) }}</dd>
                            <dt class="mt-1 text-sm font-medium text-ink-500">{{ $label }}</dt>
                        </div>
                    @endforeach
                </dl>
            </section>

            {{-- ================= FEATURES ================= --}}
            <section id="features" class="mx-auto max-w-7xl scroll-mt-8 px-4 py-24 sm:px-6 lg:px-8" aria-labelledby="features-heading">
                <div class="max-w-2xl">
                    <p class="eyebrow">Features</p>
                    <h2 id="features-heading" class="mt-3 text-3xl font-extrabold tracking-tight text-ink-900 sm:text-4xl">Everything you need to trust your stock data</h2>
                    <p class="mt-4 text-lg text-ink-500">From the moment a batch is produced to the moment it is sold, recalled or disposed of.</p>
                </div>

                <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ([
                        ['Products & batches', 'Register products with unique codes, then record each manufacturing batch with its dates and exact quantities.', 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z', 'bg-brand-50 text-brand-600'],
                        ['Shipments between locations', 'Dispatch stock from a factory to a warehouse or shop. It is tracked in transit until it is received or cancelled.', 'M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12', 'bg-sky-50 text-sky-500'],
                        ['Expiry intelligence', 'Expired and soon-to-expire batches are flagged automatically, so stock is moved or disposed of in time.', 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z', 'bg-sun-200/60 text-sun-600'],
                        ['One-click recalls', 'Recall a batch with a recorded reason. It instantly stops being shippable everywhere in the chain.', 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z', 'bg-red-50 text-red-600'],
                        ['Tamper-proof ledger', 'Every quantity change is an append-only movement. The database itself refuses edits and deletions.', 'M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z', 'bg-ink-100 text-ink-700'],
                        ['Full audit trail', 'See who changed what, when and from where. Built for accountability with regulators and partners.', 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z', 'bg-emerald-50 text-emerald-600'],
                    ] as [$title, $body, $icon, $chip])
                        <article class="group rounded-2xl bg-white p-6 ring-1 ring-ink-100 transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-ink-900/5 hover:ring-brand-200">
                            <span class="inline-flex size-11 items-center justify-center rounded-xl {{ $chip }}">
                                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" /></svg>
                            </span>
                            <h3 class="mt-5 text-lg font-bold text-ink-900">{{ $title }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-ink-500">{{ $body }}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            {{-- ================= HOW IT WORKS ================= --}}
            <section id="how-it-works" class="scroll-mt-8 bg-canvas py-24" aria-labelledby="how-heading">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl text-center">
                        <p class="eyebrow">How it works</p>
                        <h2 id="how-heading" class="mt-3 text-3xl font-extrabold tracking-tight text-ink-900 sm:text-4xl">Three steps to a traceable supply chain</h2>
                    </div>

                    <ol class="mt-16 grid gap-8 lg:grid-cols-3">
                        @foreach ([
                            ['Register', 'Add your organisations and locations, your products, and each batch where it is produced.'],
                            ['Move', 'Ship batches between locations. Receiving confirms delivery; sales and losses are recorded as removals.'],
                            ['Trace', 'Open any batch to see where its stock is right now and every step it took to get there.'],
                        ] as $i => [$title, $body])
                            <li class="relative rounded-2xl bg-white p-8 ring-1 ring-ink-100">
                                <span class="flex size-12 items-center justify-center rounded-full bg-ink-950 text-lg font-extrabold text-sun-400">{{ $i + 1 }}</span>
                                <h3 class="mt-6 text-xl font-bold text-ink-900">{{ $title }}</h3>
                                <p class="mt-2 leading-relaxed text-ink-500">{{ $body }}</p>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>

            {{-- ================= WHO IT'S FOR ================= --}}
            <section id="who-its-for" class="mx-auto max-w-7xl scroll-mt-8 px-4 py-24 sm:px-6 lg:px-8" aria-labelledby="who-heading">
                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <div>
                        <p class="eyebrow">Who it's for</p>
                        <h2 id="who-heading" class="mt-3 text-3xl font-extrabold tracking-tight text-ink-900 sm:text-4xl">Made for the way goods move in Rwanda</h2>
                        <p class="mt-4 text-lg text-ink-500">
                            Locations are organised by Rwanda's 30 districts and organisations by RRA TIN.
                            Quantities are exact to three decimals, whether you count tablets, bags or tonnes.
                        </p>
                        <ul class="mt-8 space-y-4">
                            @foreach ([
                                ['Food & beverage producers', 'Track flour, milk and juice from mill or plant to every shop.'],
                                ['Pharmaceutical distributors', 'Know exactly which pharmacies hold a batch before you recall it.'],
                                ['Agricultural cooperatives', 'Follow coffee, tea and produce from collection point to export.'],
                            ] as [$who, $what])
                                <li class="flex gap-4">
                                    <svg class="mt-0.5 size-6 shrink-0 text-brand-600" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" /></svg>
                                    <div>
                                        <p class="font-bold text-ink-900">{{ $who }}</p>
                                        <p class="text-ink-500">{{ $what }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="relative overflow-hidden rounded-3xl bg-ink-950 p-8 text-white sm:p-10">
                        <div class="bg-grid-dark absolute inset-0" aria-hidden="true"></div>
                        <div class="absolute -right-20 -bottom-20 size-72 rounded-full bg-brand-600/40 blur-3xl" aria-hidden="true"></div>
                        <div class="relative">
                            <p class="text-sm font-semibold text-sky-300">When a recall happens</p>
                            <p class="mt-4 text-2xl leading-snug font-bold">
                                “Which shops still hold batch <span class="font-mono whitespace-nowrap text-sun-300">UZ-PCM-2026-0098</span>?”
                            </p>
                            <p class="mt-4 text-ink-200">
                                Without traceability, that question takes days of phone calls. With ProductSphere, the batch page
                                shows every location holding stock, and every shipment still on the road, the moment you open it.
                            </p>
                            <div class="mt-8 grid grid-cols-2 gap-3 text-sm">
                                <div class="rounded-xl bg-white/5 p-4 ring-1 ring-white/10">
                                    <p class="text-2xl font-extrabold text-sun-400">Seconds</p>
                                    <p class="text-ink-300">to locate affected stock</p>
                                </div>
                                <div class="rounded-xl bg-white/5 p-4 ring-1 ring-white/10">
                                    <p class="text-2xl font-extrabold text-sky-300">100%</p>
                                    <p class="text-ink-300">of movements on record</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ================= CALL TO ACTION ================= --}}
            <section class="px-4 pb-24 sm:px-6 lg:px-8" aria-labelledby="cta-heading">
                <div class="relative mx-auto max-w-5xl overflow-hidden rounded-3xl bg-gradient-to-br from-brand-700 via-brand-600 to-sky-500 px-6 py-16 text-center text-white shadow-2xl shadow-brand-600/30 sm:px-12">
                    <div class="bg-grid-dark absolute inset-0 opacity-60" aria-hidden="true"></div>
                    <div class="relative">
                        <h2 id="cta-heading" class="text-3xl font-extrabold tracking-tight sm:text-4xl">See it with real supply-chain data</h2>
                        <p class="mx-auto mt-4 max-w-xl text-lg text-brand-50">
                            Create a free account and explore a working supply chain: mills, warehouses, pharmacies and shops across Rwanda.
                        </p>
                        <div class="mt-8 flex flex-wrap justify-center gap-3">
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn btn-accent px-6 py-3 text-base">Create free account</a>
                            @endif
                            <a href="{{ route('login') }}" class="btn btn-ghost-dark px-6 py-3 text-base">I already have an account</a>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-ink-100">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 py-8 text-sm text-ink-400 sm:flex-row sm:px-6 lg:px-8">
                <x-logo />
                <p>Product traceability for Rwandan supply chains · Built with Laravel</p>
            </div>
        </footer>
    </body>
</html>

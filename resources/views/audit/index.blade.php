@php
    $groupIcons = [
        'product' => 'M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9',
        'batch' => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z',
        'stock' => 'M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z',
        'shipment' => 'M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12',
        'organization' => 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21',
        'location' => 'M15 10.5a3 3 0 11-6 0 3 3 0 016 0z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z',
    ];
    $tone = fn (string $event) => match (true) {
        $event === 'batch.recalled' => 'bg-clay-100 text-clay-700',
        str_starts_with($event, 'stock.') => 'bg-honey-100 text-honey-700',
        str_ends_with($event, '.created') => 'bg-brand-100 text-brand-700',
        default => 'bg-ink-100 text-ink-600',
    };
@endphp

<x-layouts.app title="Audit log">
    <form method="GET" action="{{ route('audit.index') }}" role="search" class="mb-6 flex flex-wrap items-center gap-2">
        <a href="{{ route('audit.index') }}" @class(['chip', 'bg-brand-800 text-brand-50' => $event === '', 'hover:bg-ink-200' => $event !== ''])>All</a>
        @foreach ($eventGroups as $group)
            <a href="{{ route('audit.index', ['event' => $group.'.']) }}"
               @class(['chip', 'bg-brand-800 text-brand-50' => $event === $group.'.', 'hover:bg-ink-200' => $event !== $group.'.'])>
                {{ ucfirst($group) }}
            </a>
        @endforeach
    </form>

    <p class="mb-5 max-w-2xl text-sm text-ink-500">
        Every change, with who made it, when and from where. Entries can't be edited or deleted, not even by administrators.
    </p>

    @if ($logs->isEmpty())
        <div class="card"><x-empty-state title="No audit entries yet" message="Changes to products, batches, shipments and the supply chain appear here." /></div>
    @else
        <div class="space-y-8">
            @foreach ($logs->groupBy(fn ($log) => $log->created_at->toDateString()) as $day => $dayLogs)
                @php($date = \Illuminate\Support\Carbon::parse($day))
                <section aria-label="{{ $date->format('j F Y') }}">
                    <h2 class="mb-3 px-1 text-sm font-medium text-ink-500">
                        {{ $date->isToday() ? 'Today' : ($date->isYesterday() ? 'Yesterday' : $date->format('l, j F Y')) }}
                    </h2>

                    <ol class="card divide-y divide-ink-100">
                        @foreach ($dayLogs as $log)
                            @php($changes = $log->displayChanges())
                            <li class="flex gap-4 px-5 py-4 sm:px-6">
                                <span class="mt-0.5 flex size-10 shrink-0 items-center justify-center rounded-full {{ $tone($log->event) }}" aria-hidden="true">
                                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $groupIcons[$log->group()] ?? $groupIcons['product'] }}" />
                                    </svg>
                                </span>

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-col gap-1 sm:flex-row sm:items-baseline sm:justify-between sm:gap-4">
                                        <p class="text-sm leading-relaxed break-words text-ink-700">
                                            <span class="font-medium text-ink-900">{{ $log->user?->name ?? 'System' }}</span>
                                            {{ $log->phrase() }}
                                            @if ($label = $log->subjectLabel())
                                                @if ($url = $log->subjectUrl())
                                                    <a href="{{ $url }}" class="link">{{ $label }}</a>
                                                @else
                                                    <span class="font-medium text-ink-900">{{ $label }}</span>
                                                @endif
                                            @endif
                                        </p>
                                        <time class="shrink-0 text-xs text-ink-400" datetime="{{ $log->created_at->toIso8601String() }}" title="{{ $log->created_at->format('d M Y, H:i:s') }}">
                                            {{ $log->created_at->format('H:i') }} · {{ $log->created_at->diffForHumans() }}
                                        </time>
                                    </div>

                                    <p class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-ink-400">
                                        <span class="font-mono">{{ $log->event }}</span>
                                        @if ($log->ip_address)
                                            <span>IP {{ $log->ip_address }}</span>
                                        @endif
                                    </p>

                                    @if ($changes !== [])
                                        @if ($log->isCreation())
                                            <details class="group mt-3">
                                                <summary class="inline-flex cursor-pointer list-none items-center gap-1 rounded-full text-xs font-medium text-brand-700 hover:text-brand-600 [&::-webkit-details-marker]:hidden">
                                                    <svg class="size-3.5 transition group-open:rotate-90" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
                                                    <span class="group-open:hidden">Show details</span>
                                                    <span class="hidden group-open:inline">Hide details</span>
                                                </summary>
                                                <dl class="mt-3 grid grid-cols-1 gap-x-6 gap-y-2 rounded-2xl bg-ink-50 p-4 text-sm sm:grid-cols-2">
                                                    @foreach ($changes as $change)
                                                        <div class="min-w-0">
                                                            <dt class="text-xs text-ink-400">{{ $change['label'] }}</dt>
                                                            <dd class="break-words text-ink-800">{{ $change['new'] }}</dd>
                                                        </div>
                                                    @endforeach
                                                </dl>
                                            </details>
                                        @else
                                            <ul class="mt-3 space-y-1.5 rounded-2xl bg-ink-50 p-4 text-sm">
                                                @foreach ($changes as $change)
                                                    <li class="flex flex-wrap items-baseline gap-x-2 gap-y-0.5 break-words">
                                                        <span class="text-ink-500">{{ $change['label'] }}</span>
                                                        @if ($change['old'] !== null)
                                                            <span class="text-ink-400 line-through decoration-ink-300">{{ $change['old'] }}</span>
                                                            <span class="text-ink-300" aria-label="changed to">→</span>
                                                        @endif
                                                        <span class="font-medium text-ink-900">{{ $change['new'] }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endforeach
        </div>

        @if ($logs->hasPages())
            <div class="mt-6">{{ $logs->onEachSide(1)->links() }}</div>
        @endif
    @endif
</x-layouts.app>

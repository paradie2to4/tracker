@props(['label', 'value', 'href' => null, 'tone' => 'default', 'description' => null])

@php
    [$valueClasses, $dot] = match ($tone) {
        'warning' => ['text-honey-700', 'bg-honey-500'],
        'danger' => ['text-clay-600', 'bg-clay-500'],
        'info' => ['text-brand-700', 'bg-gold-400'],
        default => ['text-ink-900', 'bg-brand-400'],
    };
@endphp

<div class="card flex flex-col p-6">
    <div class="flex items-center justify-between gap-2">
        <dt class="flex items-center gap-2 text-sm text-ink-500">
            <span class="size-1.5 rounded-full {{ $dot }}" aria-hidden="true"></span>
            {{ $label }}
        </dt>
        @if ($href)
            <a href="{{ $href }}" class="-m-1.5 rounded-full p-1.5 text-ink-400 hover:bg-ink-100 hover:text-ink-800" aria-label="View {{ Str::lower($label) }}">
                <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            </a>
        @endif
    </div>
    <dd class="mt-4 text-4xl font-medium tracking-tight tabular-nums {{ $valueClasses }}">{{ number_format($value) }}</dd>
    @if ($description)
        <p class="mt-1.5 text-xs text-ink-400">{{ $description }}</p>
    @endif
</div>

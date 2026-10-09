@props(['label', 'value', 'href' => null, 'tone' => 'default', 'description' => null])

@php
    [$valueClasses, $accent] = match ($tone) {
        'warning' => ['text-amber-700', 'from-sun-400 to-amber-500'],
        'danger' => ['text-red-700', 'from-red-500 to-rose-400'],
        'info' => ['text-sky-700', 'from-sky-400 to-brand-500'],
        default => ['text-ink-900', 'from-brand-600 to-sky-500'],
    };
@endphp

<div class="card relative overflow-hidden p-5">
    <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r {{ $accent }}" aria-hidden="true"></span>
    <dt class="text-sm font-semibold text-ink-500">{{ $label }}</dt>
    <dd class="mt-2 text-3xl font-extrabold tracking-tight tabular-nums {{ $valueClasses }}">{{ number_format($value) }}</dd>
    @if ($description)
        <p class="mt-1 text-xs text-ink-400">{{ $description }}</p>
    @endif
    @if ($href)
        <a href="{{ $href }}" class="link mt-3 inline-block text-sm">View<span class="sr-only"> {{ Str::lower($label) }}</span> →</a>
    @endif
</div>

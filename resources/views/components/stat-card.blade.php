@props(['label', 'value', 'href' => null, 'tone' => 'default', 'description' => null])

@php
    $valueClasses = match ($tone) {
        'warning' => 'text-amber-700',
        'danger' => 'text-red-700',
        default => 'text-slate-900',
    };
@endphp

<div class="card p-5">
    <dt class="text-sm font-medium text-slate-500">{{ $label }}</dt>
    <dd class="mt-2 text-3xl font-semibold tracking-tight {{ $valueClasses }}">{{ number_format($value) }}</dd>
    @if ($description)
        <p class="mt-1 text-xs text-slate-500">{{ $description }}</p>
    @endif
    @if ($href)
        <a href="{{ $href }}" class="link mt-3 inline-block text-sm">View<span class="sr-only"> {{ Str::lower($label) }}</span> →</a>
    @endif
</div>

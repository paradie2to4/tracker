{{-- ProductSphere logo: three linked nodes (a batch moving through the chain). --}}
@props(['dark' => false, 'size' => 'md'])

@php
    $mark = $size === 'lg' ? 'size-10' : 'size-8';
    $text = $size === 'lg' ? 'text-xl' : 'text-lg';
    // Unique per instance: the logo can appear several times on one page.
    $gradientId = 'ps-mark-'.Str::random(8);
@endphp

<span {{ $attributes->class('inline-flex items-center gap-2.5') }}>
    <svg class="{{ $mark }} shrink-0" viewBox="0 0 40 40" fill="none" aria-hidden="true">
        <defs>
            <linearGradient id="{{ $gradientId }}" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse">
                <stop stop-color="#3a64fb" />
                <stop offset="1" stop-color="#00a1de" />
            </linearGradient>
        </defs>
        <rect width="40" height="40" rx="11" fill="url(#{{ $gradientId }})" />
        <path d="M11 27 L20 13 L29 25" stroke="#fff" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" />
        <circle cx="11" cy="27" r="3.6" fill="#fff" />
        <circle cx="20" cy="13" r="3.6" fill="#fff" />
        <circle cx="29" cy="25" r="4.2" fill="#fad201" />
    </svg>
    <span class="{{ $text }} font-extrabold tracking-tight {{ $dark ? 'text-white' : 'text-ink-900' }}">
        Product<span class="{{ $dark ? 'text-sky-400' : 'text-brand-600' }}">Sphere</span>
    </span>
</span>

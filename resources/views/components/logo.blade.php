{{-- ProductSphere logo: three linked nodes (a batch moving through the chain). --}}
@props(['size' => 'md'])

@php
    $mark = $size === 'lg' ? 'size-10' : 'size-8';
    $text = $size === 'lg' ? 'text-xl' : 'text-lg';
@endphp

<span {{ $attributes->class('inline-flex items-center gap-2.5') }}>
    <svg class="{{ $mark }} shrink-0" viewBox="0 0 40 40" fill="none" aria-hidden="true">
        <rect width="40" height="40" rx="12" fill="#36452d" />
        <path d="M11 27 L20 13 L29 25" stroke="#e3e9dc" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" />
        <circle cx="11" cy="27" r="3.4" fill="#e3e9dc" />
        <circle cx="20" cy="13" r="3.4" fill="#e3e9dc" />
        <circle cx="29" cy="25" r="4" fill="#e2bd5f" />
    </svg>
    <span class="{{ $text }} font-semibold tracking-tight text-ink-900">
        Product<span class="font-normal text-brand-600">Sphere</span>
    </span>
</span>

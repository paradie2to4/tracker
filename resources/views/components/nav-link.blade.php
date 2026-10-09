@props(['href', 'active' => false, 'icon' => null])

<a href="{{ $href }}"
   @if ($active) aria-current="page" @endif
   {{ $attributes->class([
       'group flex items-center gap-3 whitespace-nowrap rounded-full px-4 py-2.5 text-sm transition focus-visible:outline-2 focus-visible:outline-brand-700',
       'bg-brand-800 font-medium text-brand-50' => $active,
       'text-ink-600 hover:bg-ink-100 hover:text-ink-900' => ! $active,
   ]) }}>
    @if ($icon)
        <svg class="size-5 shrink-0 {{ $active ? 'text-gold-300' : 'text-ink-400 group-hover:text-ink-700' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
        </svg>
    @endif
    {{ $slot }}
</a>

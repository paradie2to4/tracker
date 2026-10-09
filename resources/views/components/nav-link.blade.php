@props(['href', 'active' => false, 'icon' => null])

<a href="{{ $href }}"
   @if ($active) aria-current="page" @endif
   {{ $attributes->class([
       'group relative flex items-center gap-3 whitespace-nowrap rounded-lg px-3 py-2 text-sm font-semibold transition focus-visible:outline-2 focus-visible:outline-sky-400',
       'bg-white/10 text-white' => $active,
       'text-ink-300 hover:bg-white/5 hover:text-white' => ! $active,
   ]) }}>
    @if ($active)
        <span class="absolute inset-y-1.5 left-0 hidden w-1 rounded-full bg-sun-400 md:block" aria-hidden="true"></span>
    @endif
    @if ($icon)
        <svg class="size-5 shrink-0 {{ $active ? 'text-sky-300' : 'text-ink-400 group-hover:text-ink-200' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
        </svg>
    @endif
    {{ $slot }}
</a>

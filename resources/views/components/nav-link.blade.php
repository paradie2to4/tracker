@props(['href', 'active' => false])

<a href="{{ $href }}"
   @if ($active) aria-current="page" @endif
   {{ $attributes->class([
       'block whitespace-nowrap rounded-md px-3 py-2 text-sm font-medium focus-visible:outline-2 focus-visible:outline-indigo-400',
       'bg-slate-800 text-white' => $active,
       'text-slate-300 hover:bg-slate-800 hover:text-white' => ! $active,
   ]) }}>
    {{ $slot }}
</a>

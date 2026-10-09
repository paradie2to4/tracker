@props(['active'])

<span {{ $attributes->class([
    'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset',
    'bg-emerald-50 text-emerald-700 ring-emerald-600/20' => $active,
    'bg-slate-100 text-slate-600 ring-slate-500/20' => ! $active,
]) }}>
    {{ $active ? 'Active' : 'Inactive' }}
</span>

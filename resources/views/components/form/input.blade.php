@props(['name', 'label', 'type' => 'text', 'value' => null, 'hint' => null, 'required' => false])

@php
    $id = $attributes->get('id', $name);
    $hasError = $errors->has($name);
    $describedBy = collect([$hint ? $id.'-hint' : null, $hasError ? $id.'-error' : null])->filter()->implode(' ');
@endphp

<div>
    <label for="{{ $id }}" class="block text-sm font-medium text-ink-700">
        {{ $label }}
        @unless ($required)
            <span class="font-normal text-ink-400">(optional)</span>
        @endunless
    </label>
    <input
        type="{{ $type }}"
        id="{{ $id }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        @required($required)
        @if ($hasError) aria-invalid="true" @endif
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        {{ $attributes->except('id')->class('form-control mt-1.5') }}
    >
    @if ($hint)
        <p id="{{ $id }}-hint" class="mt-1.5 text-xs text-ink-500">{{ $hint }}</p>
    @endif
    @if ($hasError)
        <p id="{{ $id }}-error" class="mt-1.5 text-sm text-clay-600">{{ $errors->first($name) }}</p>
    @endif
</div>

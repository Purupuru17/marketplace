@props([
    'name',
    'label' => null,
    'required' => false,
    'hint' => null,
    'inline' => false,
])

@php
    $hasError = $errors->has($name);
@endphp

<div {{ $attributes->merge(['class' => $inline ? 'flex items-center gap-3' : '']) }}>
    @if($inline)
        <label class="shrink-0 text-sm font-medium text-gray-700 dark:text-gray-400">
            {{ $label }} @if($required)<span class="text-error-500">*</span>@endif
        </label>
    @elseif($label)
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            {{ $label }} @if($required)<span class="text-error-500">*</span>@endif
        </label>
    @endif

    {{ $slot }}

    @if($hint && !$hasError)
        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="mt-1.5 text-xs text-error-600 dark:text-error-500">{{ $message }}</p>
    @enderror
</div>
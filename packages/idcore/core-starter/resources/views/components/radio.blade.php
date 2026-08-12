@props([
    'name',
    'value',
    'label',
    'selected' => null,
])

@php
    $inputId = 'radio-' . str_replace(['[', ']', '.'], '-', $name) . '-' . \Illuminate\Support\Str::slug((string) $value);
    $current = old($name, $selected);
@endphp

<label for="{{ $inputId }}" class="inline-flex cursor-pointer select-none items-center gap-3">
    <input type="radio" id="{{ $inputId }}" name="{{ $name }}" value="{{ $value }}" @checked((string) $current === (string) $value)
        {{ $attributes->merge(['class' => 'h-5 w-5 border-gray-300 text-primary-600 shadow-theme-xs focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900']) }}>
    <span class="text-sm font-medium text-gray-700 dark:text-gray-400">{{ $label }}</span>
</label>

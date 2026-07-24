@props([
    'name',
    'label' => null,
    'value' => null,
    'rows' => 4,
    'hint' => null,
    'required' => false,
])

@php
    $hasError = $errors->has($name);
    $inputId = 'textarea-' . str_replace(['[', ']', '.'], '-', $name);
@endphp

<div>
    @if($label)
        <label for="{{ $inputId }}" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            {{ $label }} @if($required)<span class="text-danger-500">*</span>@endif
        </label>
    @endif

    <textarea id="{{ $inputId }}" name="{{ $name }}" rows="{{ $rows }}" @if($required) required @endif
        {{ $attributes->merge(['class' => 'w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs outline-none transition placeholder:text-gray-400 focus:ring-2 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500 dark:disabled:bg-gray-800 dark:disabled:text-gray-400 ' . ($hasError ? 'border-danger-300 focus:border-danger-500 focus:ring-danger-500/10 dark:border-danger-700' : 'border-gray-200 focus:border-brand-500 focus:ring-brand-500/10 dark:border-gray-700')]) }}>{{ old($name, $value) }}</textarea>

    @if($hint && !$hasError)
        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="mt-1.5 text-xs text-danger-600 dark:text-danger-500">{{ $message }}</p>
    @enderror
</div>

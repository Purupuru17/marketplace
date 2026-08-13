@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'hint' => null,
    'required' => false,
    'icon' => null,
])

@php
    $hasError = $errors->has($name);
    $inputId = 'input-' . str_replace(['[', ']', '.'], '-', $name);
    $oldKey = str_replace(['[', ']'], ['.', ''], $name);
@endphp

<div>
    @if($label)
        <label for="{{ $inputId }}" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            {{ $label }} @if($required)<span class="text-error-500">*</span>@endif
        </label>
    @endif

    <div class="relative">
        @if($icon)
            <span class="absolute left-4.5 top-4">
                <i class="{{ $icon }} text-gray-400 dark:text-gray-500"></i>
            </span>
        @endif
        <input type="{{ $type }}"
               id="{{ $inputId }}"
               name="{{ $name }}"
               value="{{ old($oldKey, $value) }}"
               placeholder="{{ $placeholder }}"
               @if($required) required @endif
               {{ $attributes->merge(['class' => 'h-11 w-full rounded-lg border bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs transition placeholder:text-gray-400 focus:ring-3 focus:outline-hidden disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:disabled:bg-gray-800 dark:disabled:text-gray-400 ' . ($icon ? 'pl-11.5 ' : '') . ($hasError ? 'border-error-300 focus:border-error-300 focus:ring-error-500/10 dark:border-error-700 dark:focus:border-error-800' : 'border-gray-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-gray-700 dark:focus:border-brand-800')]) }}>
    </div>

    @if($hint && !$hasError)
        <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="mt-1.5 text-xs text-error-500">{{ $message }}</p>
    @enderror
</div>

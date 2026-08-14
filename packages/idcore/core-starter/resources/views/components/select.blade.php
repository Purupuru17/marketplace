@props([
    'name',
    'label' => null,
    'options' => [],
    'selected' => null,
    'hint' => null,
    'required' => false,
    'placeholder' => 'Pilih opsi',
])

@php
    $hasError = $errors->has($name);
    $inputId = 'select-' . str_replace(['[', ']', '.'], '-', $name);
    $current = old($name, $selected);
@endphp

<div>
    @if($label)
        <label for="{{ $inputId }}" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            {{ $label }} @if($required)<span class="text-error-500">*</span>@endif
        </label>
    @endif

    <div class="relative z-20 bg-transparent dark:bg-gray-900">
        <select id="{{ $inputId }}" name="{{ $name }}" @if($required) required @endif
            {{ $attributes->merge(['class' => 'relative z-20 h-11 w-full appearance-none rounded-lg border bg-transparent py-2.5 pl-4 pr-10 text-sm text-gray-800 shadow-theme-xs outline-hidden transition focus:ring-3 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500 dark:bg-gray-900 dark:text-white/90 dark:disabled:bg-gray-800 dark:disabled:text-gray-400 ' . ($hasError ? 'border-error-300 focus:border-error-300 focus:ring-error-500/10 dark:border-error-700 dark:focus:border-error-800' : 'border-gray-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-gray-700 dark:focus:border-brand-800')]) }}>
            @if($placeholder)
                <option value="" class="text-gray-500 dark:text-gray-400">{{ $placeholder }}</option>
            @endif
            @foreach($options as $value => $text)
                <option value="{{ $value }}" @selected((string) $current === (string) $value) class="text-gray-800 dark:text-gray-200">{{ $text }}</option>
            @endforeach
        </select>
        <span class="pointer-events-none absolute right-3 top-1/2 z-30 -translate-y-1/2 text-gray-400 dark:text-gray-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </span>
    </div>

    @if($hint && !$hasError)
        <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="mt-1.5 text-xs text-error-500">{{ $message }}</p>
    @enderror
</div>

@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'hint' => null,
    'required' => false,
    'icon' => null,
    'state' => null,
    'successMessage' => null,
])

@php
    $hasError = $errors->has($name);
    $inputId = 'input-' . str_replace(['[', ']', '.'], '-', $name);
    $oldKey = str_replace(['[', ']'], ['.', ''], $name);

    $state = $hasError ? 'error' : ($state ?? null);

    $stateClasses = match($state) {
        'error' => 'border-error-300 focus:border-error-300 focus:ring-error-500/10 dark:border-error-700 dark:focus:border-error-800',
        'success' => 'border-success-300 focus:border-success-300 focus:ring-success-500/10 dark:border-success-700 dark:focus:border-success-800',
        default => 'border-gray-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-gray-700 dark:focus:border-brand-800',
    };

    $stateIcon = $state === 'error'
        ? '<svg class="h-4 w-4 text-error-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>'
        : ($state === 'success'
            ? '<svg class="h-4 w-4 text-success-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>'
            : null);
@endphp

<div>
    @if($label)
        <label for="{{ $inputId }}" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            {{ $label }} @if($required)<span class="text-error-500">*</span>@endif
        </label>
    @endif

    <div class="relative">
        @if($icon)
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400 dark:text-gray-500">
                @svg('heroicon-o-' . $icon, 'h-5 w-5')
            </span>
        @endif
        <input type="{{ $type }}"
               id="{{ $inputId }}"
               name="{{ $name }}"
               value="{{ old($oldKey, $value) }}"
               placeholder="{{ $placeholder }}"
               @if($required) required @endif
               {{ $attributes->merge(['class' => 'h-11 w-full rounded-lg border bg-transparent py-2.5 text-sm text-gray-800 shadow-theme-xs transition placeholder:text-gray-400 focus:ring-3 focus:outline-hidden disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:disabled:bg-gray-800 dark:disabled:text-gray-400 ' . ($icon ? 'pl-10 ' : 'pl-4 ') . ($stateIcon ? 'pr-10 ' : 'pr-4 ') . $stateClasses]) }}>
        @if($stateIcon)
            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5">
                {!! $stateIcon !!}
            </span>
        @endif
    </div>

    @if($hint && !$hasError && !$state)
        <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">{{ $hint }}</p>
    @endif
    @if($state === 'success' && $successMessage && !$hasError)
        <p class="mt-1.5 text-xs text-success-500">{{ $successMessage }}</p>
    @endif
    @error($name)
        <p class="mt-1.5 text-xs text-error-500">{{ $message }}</p>
    @enderror
</div>
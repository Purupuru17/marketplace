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
        <label for="{{ $inputId }}" class="mb-2.5 block font-medium text-gray-800 dark:text-white">
            {{ $label }} @if($required)<span class="text-danger-500">*</span>@endif
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
               {{ $attributes->merge(['class' => 'w-full rounded-lg border bg-transparent px-5 py-3 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-gray-100 dark:disabled:bg-gray-800 dark:bg-gray-900 ' . ($icon ? 'pl-11.5 ' : '') . ($hasError ? 'border-danger-500 focus:border-danger-500' : 'border-gray-200 focus:border-primary dark:border-gray-700 dark:focus:border-primary')]) }}>
    </div>

    @if($hint && !$hasError)
        <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="mt-1.5 text-xs text-danger-500">{{ $message }}</p>
    @enderror
</div>

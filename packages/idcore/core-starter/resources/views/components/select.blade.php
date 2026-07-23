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
        <label for="{{ $inputId }}" class="mb-2.5 block font-medium text-gray-800 dark:text-white">
            {{ $label }} @if($required)<span class="text-danger-500">*</span>@endif
        </label>
    @endif

    <div class="relative z-20 bg-transparent dark:bg-gray-900">
        <select id="{{ $inputId }}" name="{{ $name }}" @if($required) required @endif
            {{ $attributes->merge(['class' => 'relative z-20 w-full appearance-none rounded-lg border bg-transparent px-5 py-3 outline-none transition focus:border-primary active:border-primary dark:bg-gray-900 ' . ($hasError ? 'border-danger-500 focus:border-danger-500' : 'border-gray-200 focus:border-primary dark:border-gray-700 dark:focus:border-primary')]) }}>
            @if($placeholder)
                <option value="" class="text-gray-500 dark:text-gray-400">{{ $placeholder }}</option>
            @endif
            @foreach($options as $value => $text)
                <option value="{{ $value }}" @selected((string) $current === (string) $value) class="text-gray-800 dark:text-gray-200">{{ $text }}</option>
            @endforeach
        </select>
        <span class="absolute right-4 top-1/2 z-30 -translate-y-1/2 text-gray-400 dark:text-gray-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </span>
    </div>

    @if($hint && !$hasError)
        <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="mt-1.5 text-xs text-danger-500">{{ $message }}</p>
    @enderror
</div>

@props([
    'name',
    'label',
    'value' => '1',
    'checked' => false,
])

@php
    $inputId = 'checkbox-' . str_replace(['[', ']', '.'], '-', $name) . '-' . \Illuminate\Support\Str::slug((string) $value);
    $oldKey = rtrim(str_replace(['[', ']'], ['.', ''], $name), '.');
    $isChecked = old($oldKey, $checked);
    if (is_array($isChecked)) {
        $isChecked = in_array($value, $isChecked);
    }
@endphp

<label for="{{ $inputId }}" class="flex cursor-pointer select-none items-center gap-3" x-data="{ checked: @js((bool) $isChecked) }">
    <div class="relative">
        <input type="checkbox" id="{{ $inputId }}" name="{{ $name }}" value="{{ $value }}" x-model="checked"
            {{ $attributes->merge(['class' => 'sr-only']) }}>
        <div class="flex h-5 w-5 items-center justify-center rounded border transition-colors"
             :class="checked ? 'border-primary bg-primary' : 'border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900'">
            <svg x-show="checked" class="h-3 w-3 fill-current text-white" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M17.207 4.793a1 1 0 010 1.414l-9 9a1 1 0 01-1.414 0l-5-5a1 1 0 011.414-1.414L7.5 13.086l8.293-8.293a1 1 0 011.414 0z" />
            </svg>
        </div>
    </div>
    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</span>
</label>

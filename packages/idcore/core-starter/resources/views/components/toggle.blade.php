@props([
    'name',
    'label' => null,
    'checked' => false,
])

@php
    $inputId = 'toggle-' . str_replace(['[', ']', '.'], '-', $name);
@endphp

<div x-data="{ on: @js((bool) old($name, $checked)) }" class="flex items-center gap-3">
    <input type="hidden" id="{{ $inputId }}" name="{{ $name }}" :value="on ? 1 : 0">
    <button type="button" @click="on = !on" :aria-pressed="on"
            :class="on ? 'bg-brand-500' : 'bg-gray-300 dark:bg-gray-700'"
            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-950">
        <span :class="on ? 'translate-x-6' : 'translate-x-1'" class="inline-block h-4 w-4 rounded-full bg-white shadow-sm transition-transform"></span>
    </button>
    @if($label)
        <label for="{{ $inputId }}" class="text-sm font-medium text-gray-700 dark:text-gray-400">{{ $label }}</label>
    @endif
</div>

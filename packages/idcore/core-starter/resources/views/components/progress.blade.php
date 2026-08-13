@props([
    'value' => 0,
    'max' => 100,
    'label' => null,
    'showValue' => false,
    'variant' => 'brand',
    'size' => 'md',
])

@php
    $percent = $max > 0 ? min(100, max(0, round(($value / $max) * 100))) : 0;
    $barColor = match($variant) {
        'success' => 'bg-success-500',
        'warning' => 'bg-warning-500',
        'danger', 'error' => 'bg-error-500',
        'info' => 'bg-blue-light-500',
        'dark' => 'bg-gray-800 dark:bg-gray-600',
        'gradient' => 'bg-gradient-to-r from-brand-500 to-blue-light-500',
        default => 'bg-brand-500',
    };
    $trackHeight = $size === 'lg' ? 'h-4' : ($size === 'xs' ? 'h-1.5' : 'h-2.5');
    $barRadius = $size === 'lg' ? 'rounded-lg' : 'rounded-full';
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    @if($label || $showValue)
        <div class="mb-2 flex items-center justify-between gap-2">
            @if($label)
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</span>
            @endif
            @if($showValue)
                <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $value }} / {{ $max }}</span>
            @endif
        </div>
    @endif

    <div class="{{ $trackHeight }} w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
        <div class="{{ $barColor }} {{ $barRadius }} h-full transition-all duration-500" style="width: {{ $percent }}%"></div>
    </div>
</div>
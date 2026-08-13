@props([
    'variant' => 'gray',
    'pill' => true,
])

@php
    $variantClasses = match($variant) {
        'green', 'success' => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500',
        'red', 'danger', 'error' => 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-500',
        'yellow', 'warning' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400',
        'blue', 'info', 'indigo', 'brand' => 'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-400',
        'orange' => 'bg-orange-50 text-orange-700 dark:bg-orange-500/15 dark:text-orange-400',
        default => 'bg-gray-100 text-gray-700 dark:bg-white/5 dark:text-gray-400',
    };
    $shape = $pill ? 'rounded-full' : 'rounded-md';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold tracking-wide $variantClasses $shape"]) }}>
    {{ $slot }}
</span>

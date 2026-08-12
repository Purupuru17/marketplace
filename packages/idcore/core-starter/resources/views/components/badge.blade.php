@props([
    'variant' => 'gray',
    'pill' => true,
])

@php
    $variantClasses = match($variant) {
        'green', 'success' => 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-500',
        'red', 'danger' => 'bg-danger-50 text-danger-700 dark:bg-danger-500/10 dark:text-danger-500',
        'yellow', 'warning' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-500',
        'blue', 'info' => 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300',
        'indigo' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300',
        default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
    };
    $shape = $pill ? 'rounded-full' : 'rounded-md';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold tracking-wide $variantClasses $shape"]) }}>
    {{ $slot }}
</span>

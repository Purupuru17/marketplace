@props(['name' => '', 'size' => 'md', 'class' => ''])

@php
    $initial = strtoupper(substr($name, 0, 1)) ?: '?';
    $sizeClasses = match($size) {
        'sm' => 'h-8 w-8 text-xs',
        'lg' => 'h-20 w-20 text-3xl',
        default => 'h-10 w-10 text-sm',
    };
@endphp

<div {{ $attributes->merge(['class' => "flex items-center justify-center rounded-full border border-primary-100 bg-primary-50 font-semibold text-primary-700 shadow-theme-xs dark:border-gray-700 dark:bg-primary-500/10 dark:text-primary-300 $sizeClasses $class"]) }}>
    {{ $initial }}
</div>

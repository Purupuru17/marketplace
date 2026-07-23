@props(['href' => null, 'variant' => null, 'danger' => false])

@php
    $variant = $danger ? 'danger' : $variant;
    $color = match($variant) {
        'success' => 'text-success-700 hover:bg-success-50 dark:text-success-500 dark:hover:bg-success-500/10',
        'danger' => 'text-danger-600 hover:bg-danger-50 dark:text-danger-500 dark:hover:bg-danger-500/10',
        'warning' => 'text-warning-700 hover:bg-warning-50 dark:text-warning-500 dark:hover:bg-warning-500/10',
        default => 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800',
    };
    $classes = "flex w-full items-center gap-2 rounded-lg px-3 py-2.5 text-left text-sm font-medium transition $color";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="submit" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif

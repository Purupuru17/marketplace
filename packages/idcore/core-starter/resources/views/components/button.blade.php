@props([
    'variant' => 'primary',
    'size'    => 'md',
    'href'    => null,
    'type'    => 'button',
    'loading' => false,
    'block'   => false,
    'pill'    => false,
    'circle'  => false,
    'tooltip' => null,
])

@php
    $variantClasses = match($variant) {
        'primary'   => 'bg-primary text-white hover:bg-primary-700 focus:ring-primary/30',
        'secondary' => 'bg-secondary text-white hover:bg-blue-300 focus:ring-blue-300/30',
        'danger'    => 'bg-danger text-white hover:bg-danger-700 focus:ring-danger/30',
        'success'   => 'bg-success text-white hover:bg-success-700 focus:ring-success/30',
        'warning'   => 'bg-warning text-white hover:bg-warning-700 focus:ring-warning/30',
        'light'     => 'bg-gray-100 text-gray-700 hover:bg-gray-200 focus:ring-gray-300/30 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700',
        'dark'      => 'bg-gray-900 text-white hover:bg-gray-800 focus:ring-gray-700/30 dark:bg-gray-950 dark:hover:bg-gray-800',
        'outline'   => 'border border-primary text-primary hover:bg-primary hover:text-white dark:border-primary-500 dark:text-primary-400 dark:hover:bg-primary-600 dark:hover:text-white focus:ring-primary/30',
        'outline-danger' => 'border border-danger text-danger hover:bg-danger hover:text-white dark:border-danger-500 dark:text-danger-400 dark:hover:bg-danger-600 dark:hover:text-white focus:ring-danger/30',
        'outline-warning' => 'border border-warning text-warning hover:bg-warning hover:text-white dark:border-warning-500 dark:text-warning-400 dark:hover:bg-warning-600 dark:hover:text-white focus:ring-warning/10',
        'outline-success' => 'border border-success text-success hover:bg-success hover:text-white dark:border-success-500 dark:text-success-400 dark:hover:bg-success-600 dark:hover:text-white focus:ring-success/30',
        'ghost'     => 'text-primary hover:bg-primary-50 dark:text-primary-400 dark:hover:bg-gray-800 focus:ring-primary/30',
        default     => 'bg-primary text-white hover:bg-primary-700 focus:ring-primary/30',
    };

    $sizeClasses = match($size) {
        'xs' => $circle ? 'h-7 w-7 p-0 text-xs' : 'py-1.5 px-3 text-xs',
        'sm' => $circle ? 'h-9 w-9 p-0 text-xs' : 'py-2 px-4 text-xs',
        'lg' => $circle ? 'h-11 w-11 p-0 text-base' : 'py-3 px-6 text-sm',
        default => $circle ? 'h-10 w-10 p-0 text-sm' : 'py-2.5 px-4 text-sm',
    };

    $baseClasses = 'inline-flex items-center justify-center gap-2 text-center font-medium duration-200 ease-in-out rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';
    $shapeClasses = $pill || $circle ? 'rounded-full' : '';
    $widthClasses = $block ? 'w-full' : '';
    $loadingClasses = $loading ? 'opacity-50 pointer-events-none' : '';
    $mergedClasses = "$baseClasses $variantClasses $sizeClasses $shapeClasses $widthClasses $loadingClasses";
@endphp

@if($tooltip)
    <div class="group relative inline-flex">
@endif

@if($href)
    <a href="{{ $loading ? '#' : $href }}"
       {{ $attributes->merge(['class' => $mergedClasses]) }}>
        @if($loading)
            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}"
            {{ $attributes->merge(['class' => $mergedClasses]) }}
            @if($loading || $attributes->has('disabled')) disabled @endif>
        @if($loading)
            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        @endif
        {{ $slot }}
    </button>
@endif

@if($tooltip)
        <span class="pointer-events-none absolute -top-8 left-1/2 z-50 -translate-x-1/2 whitespace-nowrap rounded-md bg-gray-900 px-2 py-1 text-xs text-white opacity-0 shadow-sm transition group-hover:opacity-100 dark:bg-gray-700">{{ $tooltip }}</span>
    </div>
@endif

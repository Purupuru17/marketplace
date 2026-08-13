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
        'primary'   => 'bg-brand-500 text-white shadow-theme-xs hover:bg-brand-600 focus:ring-brand-500/30',
        'secondary' => 'bg-blue-light-500 text-white shadow-theme-xs hover:bg-blue-light-600 focus:ring-blue-light-500/30',
        'danger'    => 'bg-error-500 text-white shadow-theme-xs hover:bg-error-600 focus:ring-error-500/30',
        'success'   => 'bg-success-500 text-white shadow-theme-xs hover:bg-success-600 focus:ring-success-500/30',
        'warning'   => 'bg-warning-500 text-white shadow-theme-xs hover:bg-warning-600 focus:ring-warning-500/30',
        'light'     => 'bg-gray-100 text-gray-700 hover:bg-gray-200 focus:ring-gray-300/30 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10',
        'dark'      => 'bg-gray-800 text-white hover:bg-gray-900 focus:ring-gray-700/30 dark:bg-gray-950 dark:hover:bg-gray-800',
        'outline'   => 'border border-brand-200 text-brand-700 hover:bg-brand-50 dark:border-brand-500/40 dark:text-brand-300 dark:hover:bg-brand-500/10 focus:ring-brand-500/30',
        'outline-danger' => 'border border-error-200 text-error-700 hover:bg-error-50 dark:border-error-500/40 dark:text-error-300 dark:hover:bg-error-500/10 focus:ring-error-500/30',
        'outline-warning' => 'border border-warning-200 text-warning-700 hover:bg-warning-50 dark:border-warning-500/40 dark:text-warning-300 dark:hover:bg-warning-500/10 focus:ring-warning-500/20',
        'outline-success' => 'border border-success-200 text-success-700 hover:bg-success-50 dark:border-success-500/40 dark:text-success-300 dark:hover:bg-success-500/10 focus:ring-success-500/30',
        'ghost'     => 'text-brand-500 hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-gray-800 focus:ring-brand-500/30',
        default     => 'bg-brand-500 text-white shadow-theme-xs hover:bg-brand-600 focus:ring-brand-500/30',
    };

    $sizeClasses = match($size) {
        'xs' => $circle ? 'h-7 w-7 p-0 text-xs' : 'py-1.5 px-3 text-xs',
        'sm' => $circle ? 'h-9 w-9 p-0 text-xs' : 'py-2 px-4 text-xs',
        'lg' => $circle ? 'h-11 w-11 p-0 text-base' : 'py-3 px-6 text-sm',
        default => $circle ? 'h-10 w-10 p-0 text-sm' : 'py-2.5 px-4 text-sm',
    };

    $baseClasses = 'inline-flex items-center justify-center gap-2 text-center font-medium duration-200 ease-in-out rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-950 disabled:cursor-not-allowed disabled:opacity-50';
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

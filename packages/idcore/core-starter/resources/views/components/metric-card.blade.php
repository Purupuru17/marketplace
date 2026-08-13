@props([
    'label',
    'value',
    'icon' => 'squares-2x2',
    'change' => null,
    'changeTone' => 'success',
    'iconVariant' => 'brand',
    'footer' => null,
])

@php
    $iconClasses = match($iconVariant) {
        'brand'   => 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400',
        'success' => 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400',
        'warning' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400',
        'danger'  => 'bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400',
        'info'    => 'bg-blue-light-50 text-blue-light-600 dark:bg-blue-light-500/10 dark:text-blue-light-400',
        default   => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200',
    };
    $isUp = $changeTone === 'success';
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03]']) }}>
    <div class="flex items-start justify-between">
        <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl {{ $iconClasses }}">
            @svg('heroicon-o-' . $icon, 'h-6 w-6')
        </div>
        @if($change)
            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold {{ $isUp ? 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-500' : 'bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-500' }}">
                @if($isUp)
                    @svg('heroicon-o-arrow-trending-up', 'h-3.5 w-3.5')
                @else
                    @svg('heroicon-o-arrow-trending-down', 'h-3.5 w-3.5')
                @endif
                {{ $change }}
            </span>
        @endif
    </div>

    <div class="mt-7">
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $label }}</p>
        <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $value }}</p>
        @if($footer)
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ $footer }}</p>
        @endif
    </div>
</div>
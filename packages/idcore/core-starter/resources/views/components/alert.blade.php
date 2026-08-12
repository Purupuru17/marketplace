@props([
    'variant' => 'info',
    'dismissible' => true,
])

@php
    $classes = match($variant) {
        'success' => 'border-success-200 bg-success-50 text-success-800 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-500',
        'error' => 'border-danger-200 bg-danger-50 text-danger-800 dark:border-danger-500/20 dark:bg-danger-500/10 dark:text-danger-500',
        'warning' => 'border-warning-200 bg-warning-50 text-warning-800 dark:border-warning-500/20 dark:bg-warning-500/10 dark:text-warning-500',
        default => 'border-brand-200 bg-brand-50 text-brand-800 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300',
    };

    $iconName = match($variant) {
        'success' => 'heroicon-o-check-circle',
        'error' => 'heroicon-o-x-circle',
        'warning' => 'heroicon-o-exclamation-triangle',
        default => 'heroicon-o-information-circle',
    };
@endphp

<div @if($dismissible) x-data="{ show: true }" x-show="show" x-transition @endif
     {{ $attributes->merge(['class' => "flex items-start gap-3 rounded-xl border px-4 py-3 text-sm shadow-theme-xs $classes"]) }}>
    <span class="mt-0.5 shrink-0">@svg($iconName, 'h-5 w-5')</span>
    <div class="flex-1 leading-6">{{ $slot }}</div>
    @if($dismissible)
        <button type="button" @click="show = false" class="text-current opacity-60 transition hover:opacity-100 dark:hover:opacity-80">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>
    @endif
</div>

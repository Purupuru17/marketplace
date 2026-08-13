@props([
    'title' => null,
    'subtitle' => null,
    'description' => null,
    'columns' => 1,
    'footer' => false,
])

@php
    $gridClass = match ((int) $columns) {
        2 => 'grid gap-5 sm:grid-cols-2',
        3 => 'grid gap-5 sm:grid-cols-2 lg:grid-cols-3',
        default => 'grid gap-5',
    };
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03]']) }}>
    @if($title || $subtitle)
        <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800 sm:px-6 sm:py-5">
            <h4 class="text-base font-medium text-gray-800 dark:text-white/90">{{ $title }}</h4>
            @if($subtitle)
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $subtitle }}</p>
            @endif
        </div>
    @endif

    <div class="p-5 sm:p-6">
        @if($description)
            <p class="mb-5 text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
        @endif

        <div class="{{ $gridClass }}">
            {{ $slot }}
        </div>

        @if(isset($footer))
            <div class="mt-6 border-t border-gray-100 pt-5 dark:border-gray-800">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
@props([
    'title' => null,
    'subtitle' => null,
    'padding' => true,
])

<div {{ $attributes->merge(['class' => 'rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900']) }}>
    @if($title || $subtitle || isset($actions))
        <div class="border-b border-gray-200 px-4 py-4 dark:border-gray-700 sm:px-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    @if($title)
                        <h4 class="font-semibold text-gray-900 dark:text-white text-base">{{ $title }}</h4>
                    @endif
                    @if($subtitle)
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $subtitle }}</p>
                    @endif
                </div>
                @isset($actions)
                    <div class="flex shrink-0 items-center gap-2">{{ $actions }}</div>
                @endisset
            </div>
        </div>
    @endif

    <div class="{{ $padding ? 'p-4 sm:p-6' : '' }}">
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="border-t border-gray-200 px-4 py-4 dark:border-gray-700 sm:px-6">
            {{ $footer }}
        </div>
    @endisset
</div>

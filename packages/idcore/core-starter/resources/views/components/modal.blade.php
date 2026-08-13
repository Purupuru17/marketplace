@props(['maxWidth' => 'md'])

@php
    $widthClass = match($maxWidth) {
        'sm'  => 'max-w-sm',
        'lg'  => 'max-w-lg',
        'xl'  => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        default => 'max-w-md',
    };
@endphp

<div x-data="{ open: false }" x-modelable="open"
     @keydown.escape.window="open = false">
    @isset($trigger)
        <div @click="open = true">
            {{ $trigger }}
        </div>
    @endisset

    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div x-show="open" x-transition.opacity
                 class="fixed inset-0 bg-gray-900/50 dark:bg-gray-950/80" @click="open = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="open" x-transition @click.outside="open = false"
                     class="relative w-full {{ $widthClass }} rounded-2xl border border-gray-200 bg-white shadow-theme-xl dark:border-gray-800 dark:bg-gray-900">

                    <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5 dark:border-gray-800">
                        <h5 class="font-medium text-gray-800 dark:text-white/90">{{ $title ?? '' }}</h5>
                        <button @click="open = false" type="button"
                                class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>

                    <div class="p-5 text-gray-700 dark:text-gray-300">{{ $slot }}</div>

                    @isset($footer)
                        <div class="flex justify-end gap-2 border-t border-gray-100 bg-gray-50 px-5 py-3 dark:border-gray-800 dark:bg-gray-900">
                            {{ $footer }}
                        </div>
                    @endisset
                </div>
            </div>
        </div>
    </template>
</div>

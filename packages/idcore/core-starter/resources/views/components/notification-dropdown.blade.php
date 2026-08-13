@props([
    'items' => [],
    'unread' => 0,
    'title' => 'Notifications',
    'emptyMessage' => 'Belum ada notifikasi.',
])

<div x-data="{ open: false, unread: {{ $unread }} }" class="relative">
    <button class="flex items-center justify-center h-10 w-10 text-gray-500 rounded-lg hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
            @click.prevent="open = !open" aria-label="{{ $title }}">
        @svg('heroicon-o-bell', 'h-5 w-5')
        <span x-show="unread > 0" x-cloak
              class="absolute inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-error-500 px-1.5 text-xs font-semibold text-white -top-0.5 -right-0.5 ring-2 ring-white dark:ring-gray-900"
              x-text="unread"></span>
    </button>

    <div x-show="open" x-cloak x-transition @click.outside="open = false"
         class="absolute right-0 z-50 mt-2 w-80 rounded-xl border border-gray-200/80 bg-white shadow-theme-lg dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-gray-800">
            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $title }}</p>
            <button type="button" @click="unread = 0" class="text-xs font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400">
                Mark as read
            </button>
        </div>

        <div class="max-h-80 overflow-y-auto p-2 custom-scrollbar">
            @forelse($items as $item)
                <a href="{{ $item['url'] ?? '#' }}" class="flex items-start gap-3 rounded-lg px-3 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-800">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                        @svg('heroicon-o-' . ($item['icon'] ?? 'bell-alert'), 'h-5 w-5')
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ $item['title'] ?? '' }}</p>
                        <p class="mt-0.5 line-clamp-2 text-xs text-gray-500 dark:text-gray-400">{{ $item['message'] ?? '' }}</p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ $item['time'] ?? '' }}</p>
                    </div>
                    @if(!empty($item['unread']))
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-brand-500"></span>
                    @endif
                </a>
            @empty
                <div class="flex flex-col items-center gap-2 px-4 py-10 text-center">
                    @svg('heroicon-o-bell-snooze', 'h-10 w-10 text-gray-300 dark:text-gray-600')
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $emptyMessage }}</p>
                </div>
            @endforelse
        </div>

        <div class="border-t border-gray-100 p-2 dark:border-gray-800">
            <a href="#" class="block rounded-lg px-3 py-2 text-center text-sm font-medium text-brand-500 hover:bg-brand-50 hover:text-brand-600 dark:text-brand-400 dark:hover:bg-gray-800">
                View all notifications
            </a>
        </div>
    </div>
</div>
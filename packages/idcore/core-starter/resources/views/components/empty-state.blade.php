@props([
    'icon' => 'inbox',
    'title' => 'Belum ada data',
    'message' => null,
    'compact' => false,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center px-4 text-center ' . ($compact ? 'py-8' : 'py-16')]) }}>
    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
        @svg('heroicon-o-' . $icon, 'h-8 w-8')
    </div>
    <h3 class="mt-4 text-base font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
    @if($message)
        <p class="mt-1 max-w-sm text-sm text-gray-500 dark:text-gray-400">{{ $message }}</p>
    @endif
    @if(isset($actions))
        <div class="mt-5 flex items-center gap-2">{{ $actions }}</div>
    @endif
</div>
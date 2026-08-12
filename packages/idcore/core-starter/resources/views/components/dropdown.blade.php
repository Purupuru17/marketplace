@props(['align' => 'right'])

@php
    $alignClass = $align === 'left' ? 'left-0' : 'right-0';
@endphp

<div x-data="{ open: false }" class="relative inline-block text-left">
    <div @click="open = !open">
        {{ $trigger }}
    </div>

    <div x-show="open" x-cloak @click.outside="open = false" x-transition.origin.top.right
         class="absolute {{ $alignClass }} z-40 mt-2 w-52 overflow-hidden rounded-xl border border-gray-200/80 bg-white p-1.5 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900">
        {{ $slot }}
    </div>
</div>

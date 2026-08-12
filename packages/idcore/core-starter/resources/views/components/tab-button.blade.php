@props(['value'])

<button type="button" @click="tab = '{{ $value }}'"
        :class="tab === '{{ $value }}'
            ? 'border-primary-600 text-primary-600 dark:text-primary-400 dark:border-primary-400'
            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:border-gray-600'"
        class="px-5 py-3 border-b-2 text-sm font-semibold transition -mb-px hover:bg-gray-100 dark:hover:bg-gray-800 rounded-t-lg">
    {{ $slot }}
</button>

<div
    x-data
    class="fixed top-4 right-4 z-[9999] flex flex-col gap-3 w-full max-w-sm pointer-events-none"
>
    <template x-for="item in $store.toast.items" :key="item.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-8"
            class="pointer-events-auto flex items-start gap-3 rounded-lg border p-4 shadow-lg bg-white dark:bg-gray-800"
            :class="{
                'border-l-4 border-l-success-500 border-gray-200 dark:border-gray-700': item.type === 'success',
                'border-l-4 border-l-danger-500 border-gray-200 dark:border-gray-700': item.type === 'error',
                'border-l-4 border-l-warning-500 border-gray-200 dark:border-gray-700': item.type === 'warning',
                'border-l-4 border-l-brand-500 border-gray-200 dark:border-gray-700': item.type === 'info',
            }"
        >
            <span
                class="mt-0.5 shrink-0"
                :class="{
                    'text-success-500': item.type === 'success',
                    'text-danger-500': item.type === 'error',
                    'text-warning-500': item.type === 'warning',
                    'text-brand-500': item.type === 'info',
                }"
            >
                <template x-if="item.type === 'success'">@svg('heroicon-o-check-circle', 'h-5 w-5')</template>
                <template x-if="item.type === 'error'">@svg('heroicon-o-x-circle', 'h-5 w-5')</template>
                <template x-if="item.type === 'warning'">@svg('heroicon-o-exclamation-triangle', 'h-5 w-5')</template>
                <template x-if="item.type === 'info' || !item.type">@svg('heroicon-o-information-circle', 'h-5 w-5')</template>
            </span>

            <p class="flex-1 text-sm text-gray-700 dark:text-gray-200" x-text="item.message"></p>

            <button
                @click="$store.toast.remove(item.id)"
                class="shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            >
                @svg('heroicon-o-x-mark', 'h-4 w-4')
            </button>
        </div>
    </template>
</div>

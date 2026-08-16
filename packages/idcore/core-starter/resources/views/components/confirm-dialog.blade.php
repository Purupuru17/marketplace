<div x-data x-cloak x-show="$store.confirm.open" x-on:keydown.escape.window="$store.confirm.cancel()" class="fixed inset-0 z-[9999] overflow-y-auto" role="dialog" aria-modal="true" x-bind:aria-labelledby="$id('confirm-title')">
    <div x-show="$store.confirm.open" x-transition.opacity class="fixed inset-0 bg-gray-950/50 backdrop-blur-sm" x-on:click="$store.confirm.cancel()"></div>

    <div class="flex min-h-full items-center justify-center p-4 sm:p-6">
        <div x-show="$store.confirm.open" x-transition x-on:click.stop class="relative w-full max-w-md overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xl dark:border-gray-800 dark:bg-gray-900">
            <div class="p-6 sm:p-7">
                <div class="flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400" x-show="$store.confirm.options.variant !== 'danger'">
                        @svg('heroicon-o-exclamation-triangle', 'h-6 w-6')
                    </div>
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400" x-show="$store.confirm.options.variant === 'danger'">
                        @svg('heroicon-o-trash', 'h-6 w-6')
                    </div>
                    <div class="min-w-0 flex-1">
                        <h2 :id="$id('confirm-title')" class="text-base font-semibold text-gray-800 dark:text-white/90" x-text="$store.confirm.options.title || 'Konfirmasi'"></h2>
                        <p class="mt-2 text-lg leading-6 text-gray-500 dark:text-gray-400" x-html="$store.confirm.options.message || 'Apakah kamu yakin?'"></p>
                    </div>
                </div>
            </div>
            <div class="flex flex-col-reverse gap-3 border-t border-gray-100 bg-gray-50 px-6 py-4 sm:flex-row sm:justify-end dark:border-gray-800 dark:bg-gray-950/50">
                <button type="button" x-on:click="$store.confirm.cancel()" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-900">
                    <span x-text="$store.confirm.options.cancelText || 'Batal'"></span>
                </button>
                <button type="button" x-ref="confirmButton" x-on:click="$store.confirm.confirm()" x-init="$watch('$store.confirm.open', value => value && $nextTick(() => $refs.confirmButton.focus()))" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/30" :class="$store.confirm.options.variant === 'danger' ? 'bg-error-500 hover:bg-error-600 focus:ring-error-500/30' : ''">
                    @svg('heroicon-o-check', 'h-4 w-4')&nbsp;
                    <span x-text="$store.confirm.options.confirmText || 'Ya, Lanjutkan'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

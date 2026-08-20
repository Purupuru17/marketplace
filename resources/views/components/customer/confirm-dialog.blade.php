<div x-cloak x-show="$store.customerConfirm.open" class="fixed inset-0 z-50" @keydown.escape.window="$store.customerConfirm.cancel()">
    <div class="absolute inset-0 bg-black/40" @click="$store.customerConfirm.cancel()"></div>

    <div class="absolute inset-x-0 bottom-0 max-w-[420px] mx-auto bg-white rounded-t-2xl overflow-hidden"
         x-show="$store.customerConfirm.open"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full">
        <div class="w-10 h-1 bg-gray-200 rounded-full mx-auto mt-3"></div>

        <div class="px-5 pt-4 pb-1">
            <div class="flex items-start gap-3">
                <div x-show="$store.customerConfirm.options.variant === 'danger'"
                     class="w-10 h-10 shrink-0 rounded-full bg-red-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div x-show="$store.customerConfirm.options.variant !== 'danger'"
                     class="w-10 h-10 shrink-0 rounded-full bg-amber-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="font-bold text-[15px] text-gray-900 leading-snug" x-text="$store.customerConfirm.options.title || 'Konfirmasi'"></h2>
                    <p class="mt-1 text-sm text-gray-600 leading-relaxed" x-text="$store.customerConfirm.options.message || 'Apakah kamu yakin?'"></p>
                </div>
            </div>
        </div>

        <div class="px-5 pt-4 pb-5 flex gap-2.5">
            <button type="button" @click="$store.customerConfirm.cancel()"
                    class="flex-1 text-sm font-semibold text-gray-700 border border-gray-200 rounded-lg py-3">
                <span x-text="$store.customerConfirm.options.cancelText || 'Batal'"></span>
            </button>
            <button type="button" x-ref="confirmButton" @click="$store.customerConfirm.confirm()"
                    x-init="$watch('$store.customerConfirm.open', value => value && $nextTick(() => $refs.confirmButton.focus()))"
                    :class="$store.customerConfirm.options.variant === 'danger' ? 'bg-red-600' : 'bg-emerald-700'"
                    class="flex-1 text-sm font-semibold text-white rounded-lg py-3">
                <span x-text="$store.customerConfirm.options.confirmText || 'Ya, Lanjutkan'"></span>
            </button>
        </div>
    </div>
</div>
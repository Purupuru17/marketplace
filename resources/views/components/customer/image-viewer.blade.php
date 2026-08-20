<div x-cloak x-show="$store.customerViewer.open" class="fixed inset-0 z-[60]" @keydown.escape.window="$store.customerViewer.close()">
    <div class="absolute inset-0 bg-black/95" @click="$store.customerViewer.close()"></div>

    <div class="absolute inset-0 flex flex-col" x-show="$store.customerViewer.open"
         x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="flex items-center justify-between px-4 py-3 shrink-0">
            <span x-show="$store.customerViewer.status" x-text="$store.customerViewer.status"
                  class="rounded-full bg-white/10 px-2.5 py-1 text-[11px] font-semibold text-white"></span>
            <div class="flex items-center gap-2 ml-auto">
                <a x-show="$store.customerViewer.downloadUrl" :href="$store.customerViewer.downloadUrl" download
                   class="w-9 h-9 flex items-center justify-center rounded-full bg-white/10 text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                </a>
                <button type="button" @click="$store.customerViewer.close()"
                        class="w-9 h-9 flex items-center justify-center rounded-full bg-white/10 text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <div class="flex-1 min-h-0 overflow-auto px-4 py-2 flex items-center justify-center">
            <img :src="$store.customerViewer.src" :alt="$store.customerViewer.alt"
                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="scale-95 opacity-0" x-transition:enter-end="scale-100 opacity-100"
                 class="max-h-full max-w-full object-contain rounded-lg shadow-2xl">
        </div>

        <p class="shrink-0 text-center text-[11px] text-white/60 pb-4 pt-2" x-text="$store.customerViewer.alt || 'Bukti transfer'"></p>
    </div>
</div>
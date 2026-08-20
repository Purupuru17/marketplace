@extends('customer.layouts.app')
@section('title', 'Keranjang')

@section('content')
<div class="pb-56">

    <header class="sticky top-0 z-30 bg-white border-b border-gray-100 px-4 py-3 flex items-center gap-3">
        <a href="{{ url()->previous() && url()->previous() !== url()->current() ? url()->previous() : route('storefront.index') }}" class="w-8 h-8 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="font-bold text-[15px] text-gray-900">Keranjang</h1>
    </header>

    <main class="px-4">

        <div class="mt-3 flex items-start gap-2 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2.5">
            <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
            <p class="text-[11px] text-amber-800 leading-relaxed">Beberapa toko kemungkinan di luar radius pengiriman alamatmu — pengecekan lebih pasti dilakukan saat Checkout.</p>
        </div>

        @if($items->isEmpty())
            <div class="flex flex-col items-center justify-center text-center px-8 py-24">
                <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <p class="text-sm font-semibold text-gray-800">Keranjangmu masih kosong</p>
                <p class="text-xs text-gray-500 mt-1.5">Yuk mulai belanja dari toko-toko sekitarmu</p>
                <a href="{{ route('storefront.index') }}" class="mt-4 text-xs font-semibold text-white bg-emerald-700 rounded-lg px-5 py-2.5">Mulai Belanja</a>
            </div>
        @else
            @foreach($by_store as $group)
                @php $store = $group['store']; @endphp
                <section class="mt-4 bg-white rounded-xl border border-gray-100 overflow-hidden">
                    <div class="flex items-center gap-2.5 px-3.5 py-3 border-b border-gray-100">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-[10px] shrink-0">
                            {{ strtoupper(substr($store->store_name, 0, 2)) }}
                        </div>
                        <a href="{{ route('storefront.store', $store->slug) }}" class="min-w-0">
                            <p class="text-xs font-semibold text-gray-900 truncate flex items-center gap-1">{{ $store->store_name }}
                                @if(str_contains(strtolower($store->level?->name ?? ''), 'premium'))
                                    <span class="text-amber-500 text-[10px]">👑</span>
                                @endif
                            </p>
                        </a>
                    </div>

                    @foreach($group['items'] as $item)
                        @php $variant = $item->variant; @endphp
                        <div class="px-3.5 py-3 flex gap-3 {{ ! $loop->last ? 'border-b border-gray-50' : '' }}">
                            @php $productImage = $variant->product->images->first()?->path; @endphp
                            <a href="{{ route('storefront.product', [$store->slug, $variant->product->slug]) }}"
                               class="w-16 h-16 rounded-lg bg-gray-100 shrink-0 overflow-hidden">
                                @if($productImage)
                                    <img src="{{ asset('storage/' . $productImage) }}" alt="" class="w-full h-full object-cover">
                                @endif
                            </a>
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('storefront.product', [$store->slug, $variant->product->slug]) }}">
                                    <p class="text-[13px] font-medium text-gray-900 leading-snug line-clamp-2">{{ $variant->product->name }}</p>
                                </a>
                                <p class="text-[11px] text-gray-500 mt-0.5">
                                    @if($variant->attributeValues->isNotEmpty())
                                        {{ $variant->attributeValues->sortBy(fn ($v) => $v->attribute?->name)->pluck('value')->join(', ') }} ·
                                    @endif
                                    @if($item->promotion) <span class="text-red-600 font-medium">{{ $item->promotion->name }}</span> @endif
                                </p>
                                <div class="flex items-center justify-between mt-2">
                                    <div>
                                        @if((float) $item->unit_discount > 0)
                                            <p class="text-[10px] text-gray-400 line-through">Rp {{ number_format((float) $item->unit_original_price, 0, ',', '.') }}</p>
                                        @endif
                                        <p class="text-sm font-bold text-emerald-700 leading-none">Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</p>
                                    </div>
                                    <form method="POST" action="{{ route('customer.cart.update', $item->id) }}"
                                          x-data="{ q: {{ $item->qty }} }" data-max="{{ $variant->stock }}">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="qty" :value="q">
                                        <div class="flex items-center border border-gray-200 rounded-full">
                                            <button type="button" @click="q = q > 1 ? q - 1 : q; $nextTick(() => $el.closest('form').requestSubmit())"
                                                    :disabled="q === 1"
                                                    :class="q === 1 ? 'text-gray-300 cursor-not-allowed' : 'text-gray-500'"
                                                    class="w-7 h-7 flex items-center justify-center text-base font-medium">−</button>
                                            <span class="w-6 text-center text-xs font-semibold text-gray-900" x-text="q">{{ $item->qty }}</span>
                                            <button type="button" @click="q = q < {{ $variant->stock }} ? q + 1 : q; $nextTick(() => $el.closest('form').requestSubmit())"
                                                    class="w-7 h-7 flex items-center justify-center text-gray-700 text-base font-medium">+</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('customer.cart.destroy', $item->id) }}" class="shrink-0 self-start"
                                      x-ref="deleteForm{{ $item->id }}">
                                @csrf @method('DELETE')
                                <button type="button"
                                        @click="$customerConfirm({ title: 'Hapus item?', message: @js($variant->product->name . ' akan dihapus dari keranjang.'), confirmText: 'Ya, Hapus', variant: 'danger' }).then(ok => { if (ok) $refs['deleteForm{{ $item->id }}'].submit(); })"
                                        class="text-gray-400">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    @endforeach

                    <div class="px-3.5 py-2.5 bg-gray-50 flex items-center justify-between">
                        <span class="text-[11px] text-gray-500">Subtotal toko</span>
                        <span class="text-xs font-bold text-gray-900">Rp {{ number_format($group['subtotal'], 0, ',', '.') }}</span>
                    </div>
                </section>
            @endforeach
        @endif

    </main>

    @unless($items->isEmpty())
    <div class="fixed bottom-14 inset-x-0 max-w-[420px] mx-auto bg-white border-t border-gray-100 z-40">
        <div class="px-4 pt-3 pb-3 flex items-center justify-between">
            <div>
                <p class="text-[11px] text-gray-500">Total ({{ $by_store->count() }} toko)</p>
                <p class="text-lg font-extrabold text-gray-900">Rp {{ number_format($total, 0, ',', '.') }}</p>
            </div>
            <a href="{{ route('customer.checkout.index') }}"
               class="text-sm font-semibold text-white bg-emerald-700 rounded-lg px-6 py-3">Lanjut ke Checkout</a>
        </div>
    </div>
    @endunless

</div>
@endsection
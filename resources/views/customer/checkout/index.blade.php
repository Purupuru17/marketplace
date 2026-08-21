@extends('customer.layouts.app')
@section('title', 'Checkout')

@section('content')
<div class="pb-40" x-data="{
    addrOpen: false,
    stores: @js(collect($summary['by_store'])->mapWithKeys(function ($g) {
        return [$g['store']->id => [
            'ftype' => $g['fulfillment_type'],
            'pm' => $g['payment_method'],
            'shipCost' => (float) $g['shipping']['cost'],
            'subtotal' => (float) $g['subtotal'],
        ]];
    })->all()),
    get grandTotal() {
        return Object.values(this.stores).reduce((t, s) => t + s.subtotal + (s.ftype === 'delivery' ? s.shipCost : 0), 0);
    },
    storeTotal(id) {
        const s = this.stores[id];
        return s.subtotal + (s.ftype === 'delivery' ? s.shipCost : 0);
    },
    fmt(n) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n));
    },
    createOrder() {
        $customerConfirm({
            title: 'Buat Pesanan?',
            message: 'Pastikan alamat, metode pengiriman, dan metode bayar sudah benar.',
            confirmText: 'Ya, Buat Pesanan',
        }).then(ok => ok && this.$refs.checkoutForm.submit());
    },
}">

    <header class="sticky top-0 z-30 bg-white border-b border-gray-100 px-4 py-3 flex items-center gap-3">
        <a href="{{ route('customer.cart.index') }}" class="w-8 h-8 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="font-bold text-[15px] text-gray-900">Checkout</h1>
    </header>

    @error('address_id')
        <div class="m-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
    @enderror

    <main class="px-4">

        @php $activeAddress = $addresses->firstWhere('id', $selected_address_id); @endphp

        <section class="mt-3 bg-white rounded-xl border border-gray-100 p-3.5 flex items-start gap-3">
            <svg class="w-5 h-5 text-emerald-700 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <div class="flex-1 min-w-0">
                @if($activeAddress)
                    <p class="text-xs font-semibold text-gray-900">{{ $activeAddress->label }} 
                        @if($activeAddress->is_default)
                            <span class="ml-1 text-[10px] font-medium bg-emerald-50 text-emerald-700 rounded-full px-1.5 py-0.5">Utama</span>
                        @endif
                    </p>
                    <p class="text-[11px] text-gray-500 mt-0.5">{{ $activeAddress->full_address }}</p>
                @else
                    <p class="text-xs font-semibold text-gray-900">Alamat pengiriman</p>
                    <p class="text-[11px] text-gray-500 mt-0.5">
                        Kamu belum punya alamat. Pilih <span class="font-medium text-gray-700">Ambil Sendiri</span> untuk semua toko,
                        atau <a href="{{ route('customer.address.create') }}" class="font-medium text-emerald-700">tambah alamat</a> dulu.
                    </p>
                @endif
            </div>
            @if($addresses->isNotEmpty())
                <button type="button" @click="addrOpen = !addrOpen" class="text-xs font-semibold text-emerald-700 shrink-0 flex items-center gap-0.5">
                    Ganti
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
            @endif
        </section>

        <div x-show="addrOpen" x-cloak class="mt-2 space-y-2">
            @foreach($addresses as $address)
                <a href="{{ route('customer.checkout.index', ['address_id' => $address->id]) }}"
                   class="flex items-start gap-2 rounded-xl border p-3 {{ $selected_address_id === $address->id ? 'border-emerald-600 bg-emerald-50/50' : 'border-gray-200 bg-white' }}">
                    <svg class="w-4 h-4 {{ $selected_address_id === $address->id ? 'text-emerald-700' : 'text-gray-400' }} shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-gray-900">{{ $address->recipient_name }}
                            @if($address->is_default)
                                <span class="ml-1 text-[10px] font-medium bg-emerald-50 text-emerald-700 rounded-full px-1.5 py-0.5">Utama</span>
                            @endif
                        </p>
                        <p class="text-[11px] text-gray-500 mt-0.5">{{ $address->full_address }} · {{ $address->locationNode?->name }}</p>
                    </div>
                </a>
            @endforeach
        </div>

        <form method="POST" action="{{ route('customer.checkout.store') }}" id="checkout-form" x-ref="checkoutForm">
            @csrf
            @if($selected_address_id)
                <input type="hidden" name="address_id" value="{{ $selected_address_id }}">
            @endif

            <div class="space-y-3 mt-4">
                @foreach($summary['by_store'] as $group)
                    @php
                        $store = $group['store'];
                        $withinRadius = $group['shipping']['within_radius'];
                        $violation = $group['fulfillment_type'] === 'delivery' && ! $withinRadius && $group['shipping']['distance_km'] !== null;
                        $storeId = $store->id;
                    @endphp
                    <section class="bg-white rounded-xl overflow-hidden {{ $violation ? 'border border-red-200' : 'border border-gray-100' }}">

                        <div class="flex items-center gap-2.5 px-3.5 py-3 border-b border-gray-100">
                            <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-[10px] shrink-0">
                                {{ strtoupper(substr($store->store_name, 0, 2)) }}
                            </div>
                            <p class="text-xs font-semibold text-gray-900 truncate">{{ $store->store_name }}</p>
                        </div>

                        <div class="px-3.5 pt-3">
                            <div class="grid grid-cols-2 gap-2">
                                <label class="cursor-pointer">
                                    <input type="radio" name="stores[{{ $storeId }}][fulfillment_type]" value="pickup"
                                           @checked($group['fulfillment_type'] === 'pickup') x-model="stores['{{ $storeId }}'].ftype" class="peer sr-only">
                                    <span x-bind:class="stores['{{ $storeId }}'].ftype === 'pickup' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 bg-white text-gray-600'"
                                          class="flex items-center justify-center gap-1.5 border rounded-lg px-3 py-2.5 text-xs font-semibold">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                        Ambil Sendiri
                                    </span>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="stores[{{ $storeId }}][fulfillment_type]" value="delivery"
                                           @checked($group['fulfillment_type'] === 'delivery') x-model="stores['{{ $storeId }}'].ftype" class="peer sr-only">
                                    <span x-bind:class="stores['{{ $storeId }}'].ftype === 'delivery' ? 'border-orange-500 bg-orange-50 text-orange-700' : 'border-gray-200 bg-white text-gray-600'"
                                          class="flex items-center justify-center gap-1.5 border rounded-lg px-3 py-2.5 text-xs font-semibold">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 19a2 2 0 100-4 2 2 0 000 4zm10 0a2 2 0 100-4 2 2 0 000 4zM2 5h12v10H2zM14 9h4l3 3v3h-7z"/></svg>
                                        Diantar
                                    </span>
                                </label>
                            </div>
                        </div>

                        <template x-if="stores['{{ $storeId }}'].ftype === 'pickup'">
                            <div class="mx-3.5 mt-3 bg-blue-50 border border-blue-100 rounded-lg p-3">
                                <p class="text-[11px] text-blue-800 font-medium">{{ $store->locationNode?->name ?? 'Ambil di toko' }}</p>
                                <p class="text-[11px] text-gray-600 mt-1">Ambil sendiri langsung di toko, tanpa ongkir.</p>
                            </div>
                        </template>

                        <template x-if="stores['{{ $storeId }}'].ftype === 'delivery' && {{ $violation ? 'true' : 'false' }}">
                            <div class="mx-3.5 mt-3 bg-red-50 border border-red-200 rounded-lg p-3">
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-red-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                                    <p class="text-[11px] text-red-700 leading-relaxed">
                                        Toko ini di luar jangkauan pengiriman (jarak {{ number_format($group['shipping']['distance_km'], 1, ',', '.') }} km, maks. {{ (float) $store->max_radius_km }} km). Ganti ke <span class="font-semibold">Ambil Sendiri</span> supaya pesanan tetap bisa diproses.
                                    </p>
                                </div>
                                <button type="button" @click="stores['{{ $storeId }}'].ftype = 'pickup'"
                                        class="w-full mt-2.5 text-[11px] font-semibold text-white bg-emerald-700 rounded-lg py-2 flex items-center justify-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                    Ganti ke Ambil Sendiri
                                </button>
                            </div>
                        </template>

                        <div class="px-3.5 pt-3.5">
                            <p class="text-[13px] font-semibold text-gray-800 mb-2">Metode Pembayaran</p>
                            <div class="flex gap-2 flex-wrap">
                                @foreach($payment_methods as $key => $label)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="stores[{{ $storeId }}][payment_method]" value="{{ $key }}"
                                               @checked($group['payment_method'] === $key) x-model="stores['{{ $storeId }}'].pm" class="peer sr-only">
                                        <span class="inline-block text-xs font-medium rounded-full px-3 py-1.5 border bg-white border-gray-200 text-gray-600 peer-checked:bg-emerald-700 peer-checked:text-white peer-checked:border-emerald-700">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="text-[11px] text-gray-500 mt-2" x-text="stores['{{ $storeId }}'].ftype === 'pickup' ? 'Bayar tunai saat ambil di toko' : 'Pembayaran dikonfirmasi setelah pesanan dibuat.'"></p>

                            <div x-show="stores['{{ $storeId }}'].pm === 'bank_transfer'" x-cloak class="mt-3 rounded-xl border {{ $store->account_number ? 'border-emerald-100 bg-emerald-50' : 'border-amber-200 bg-amber-50' }} p-3.5">
                                @if($store->account_number)
                                    <p class="text-[11px] font-semibold {{ $store->account_number ? 'text-emerald-800' : 'text-amber-800' }}">Transfer ke rekening toko</p>
                                    <div class="mt-2 space-y-1.5 text-[11px]">
                                        <div class="flex justify-between"><span class="text-gray-500">Bank</span><span class="font-semibold text-gray-900">{{ $store->bank_name }}</span></div>
                                        <div class="flex justify-between"><span class="text-gray-500">No. Rekening</span><span class="font-mono font-semibold text-gray-900">{{ $store->account_number }}</span></div>
                                        <div class="flex justify-between"><span class="text-gray-500">Atas Nama</span><span class="font-semibold text-gray-900">{{ $store->account_name }}</span></div>
                                    </div>
                                    <p class="mt-2.5 text-[11px] text-gray-500 leading-relaxed">Setelah transfer, upload bukti dari halaman pesanan agar toko dapat mengonfirmasi.</p>
                                @else
                                    <div class="flex items-start gap-2">
                                        <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                                        <p class="text-[11px] text-amber-800 leading-relaxed">Toko belum melengkapi data rekening. Pilih metode lain atau hubungi toko untuk info pembayaran.</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="px-3.5 py-3 mt-1 border-t border-gray-100 space-y-1.5">
                            @foreach($group['items'] as $item)
                                <div class="flex items-center justify-between text-xs text-gray-600">
                                    <span class="line-clamp-1 pr-2">{{ $item->variant->product->name }} x{{ $item->qty }}</span>
                                    <span class="shrink-0">Rp {{ number_format((float) $item->unit_price * $item->qty, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="px-3.5 py-2.5 bg-gray-50 space-y-1">
                            <div class="flex items-center justify-between text-[11px] text-gray-500">
                                <span>Subtotal toko</span>
                                <span class="font-bold text-gray-900 text-xs">Rp {{ number_format($group['subtotal'], 0, ',', '.') }}</span>
                            </div>
                            <div x-show="stores['{{ $storeId }}'].ftype === 'delivery'" class="flex items-center justify-between text-[11px] text-gray-500">
                                <span>Ongkir {{ $group['shipping']['distance_km'] !== null ? '(' . number_format($group['shipping']['distance_km'], 1, ',', '.') . ' km)' : '' }}</span>
                                <span class="font-semibold text-gray-900 text-xs" x-text="fmt(stores['{{ $storeId }}'].shipCost)"></span>
                            </div>
                            <div x-show="stores['{{ $storeId }}'].ftype === 'pickup'" class="flex items-center justify-between text-[11px] text-gray-500">
                                <span>Ongkir</span>
                                <span class="font-semibold text-blue-700">Gratis (Ambil Sendiri)</span>
                            </div>
                            <div class="flex items-center justify-between pt-1 border-t border-gray-100">
                                <span class="text-[11px] text-gray-500">Total toko</span>
                                <span class="text-xs font-bold text-gray-900" x-text="fmt(storeTotal('{{ $storeId }}'))"></span>
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>

            </form>
    </main>

    <div class="fixed bottom-14 inset-x-0 max-w-[420px] mx-auto bg-white border-t border-gray-100 px-4 py-3 flex items-center justify-between z-40">
        <div>
            <p class="text-[11px] text-gray-500">Total Pesanan</p>
            <p class="text-lg font-extrabold text-gray-900" x-text="fmt(grandTotal)"></p>
        </div>
        <button type="button" @click="createOrder()"
                class="text-sm font-semibold text-white bg-emerald-700 rounded-lg px-6 py-3 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Buat Pesanan
        </button>
    </div>

</div>
@endsection
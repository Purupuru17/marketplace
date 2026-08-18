@extends('customer.layouts.app')
@section('title', 'Checkout')

@section('content')
<h1 class="mb-8 text-2xl font-bold text-gray-900 dark:text-white">Checkout</h1>

@error('address_id')
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">
        {{ $message }}
    </div>
@enderror

<div class="mb-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Alamat Pengiriman</h2>
        <a href="{{ route('customer.address.index') }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Kelola Alamat</a>
    </div>

    @if($addresses->isEmpty())
        <p class="rounded-2xl border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
            Kamu belum punya alamat. Pilih <span class="font-semibold text-gray-700 dark:text-gray-200">Ambil Sendiri</span> untuk semua toko,
            atau <a href="{{ route('customer.address.create') }}" class="font-medium text-indigo-600 dark:text-indigo-400">tambah alamat</a> dulu.
        </p>
    @else
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            @foreach($addresses as $address)
                <a href="{{ route('customer.checkout.index', ['address_id' => $address->id]) }}"
                   class="rounded-xl border p-4 transition @if($selected_address_id === $address->id) border-indigo-500 bg-indigo-50/50 dark:bg-indigo-500/10 @else border-gray-200 hover:border-gray-300 dark:border-gray-700 @endif">
                    <div class="flex items-center justify-between">
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $address->recipient_name }}</p>
                        @if($address->is_default)
                            <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">Utama</span>
                        @endif
                    </div>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $address->full_address }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $address->phone }} · {{ $address->locationNode?->name }}</p>
                </a>
            @endforeach
        </div>
    @endif
</div>

<form method="POST" action="{{ route('customer.checkout.store') }}">
    @csrf
    @if($selected_address_id)
        <input type="hidden" name="address_id" value="{{ $selected_address_id }}">
    @endif

    <div class="mb-6 space-y-4">
        @foreach($summary['by_store'] as $group)
            @php $storeId = $group['store']->id; @endphp
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $group['store']->store_name }}</p>
                </div>

                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($group['items'] as $item)
                        <div class="flex items-center justify-between gap-4 px-6 py-4">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $item->variant->product->name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $item->variant->sku }}
                                    @if($item->variant->attributeValues->isNotEmpty())
                                        · {{ $item->variant->attributeValues->sortBy(fn ($v) => $v->attribute?->name)->pluck('value')->join(' · ') }}
                                    @endif
                                    × {{ $item->qty }}
                                </p>
                                @if((float) $item->unit_discount > 0)
                                    <p class="mt-1 flex flex-wrap items-center gap-2 text-xs">
                                        <span class="text-gray-400 line-through dark:text-gray-500">Rp {{ number_format((float) $item->unit_original_price, 0, ',', '.') }}</span>
                                        <span class="rounded-full bg-red-50 px-2 py-0.5 font-semibold text-red-600 dark:bg-red-500/10 dark:text-red-400">
                                            {{ $item->promotion?->name }}
                                        </span>
                                    </p>
                                @endif
                            </div>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                Rp {{ number_format((float) $item->unit_price * $item->qty, 0, ',', '.') }}
                            </p>
                        </div>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 gap-6 border-t border-gray-100 px-6 py-4 sm:grid-cols-2 dark:border-gray-800">
                    <div>
                        <p class="mb-2 text-sm font-semibold text-gray-900 dark:text-white">Metode Pengambilan</p>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @foreach(['delivery' => 'Kirim / Antar', 'pickup' => 'Ambil Sendiri'] as $key => $label)
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl border p-3 text-sm transition has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/50 dark:border-gray-700 dark:has-[:checked]:bg-indigo-500/10">
                                    <input type="radio" name="stores[{{ $storeId }}][fulfillment_type]" value="{{ $key }}"
                                           @checked(old("stores.{$storeId}.fulfillment_type", $group['fulfillment_type']) === $key)
                                           class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800">
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <p class="mb-2 text-sm font-semibold text-gray-900 dark:text-white">Metode Pembayaran</p>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @foreach($payment_methods as $key => $label)
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl border p-3 text-sm transition has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/50 dark:border-gray-700 dark:has-[:checked]:bg-indigo-500/10">
                                    <input type="radio" name="stores[{{ $storeId }}][payment_method]" value="{{ $key }}"
                                           @checked(old("stores.{$storeId}.payment_method", $group['payment_method']) === $key)
                                           class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800">
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-100 px-6 py-4 text-sm dark:border-gray-800">
                    <div class="flex justify-between text-gray-600 dark:text-gray-300">
                        <span>Subtotal</span>
                        <span>Rp {{ number_format($group['subtotal'], 0, ',', '.') }}</span>
                    </div>
                    @if($group['discount'] > 0)
                        <div class="mt-1 flex justify-between text-red-600 dark:text-red-400">
                            <span>Diskon Promo</span>
                            <span>- Rp {{ number_format($group['discount'], 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="mt-1 flex justify-between text-gray-600 dark:text-gray-300">
                        <span>
                            Ongkir
                            @if($group['shipping']['distance_km'] !== null)
                                ({{ number_format($group['shipping']['distance_km'], 1, ',', '.') }} km)
                            @endif
                            @if($group['fulfillment_type'] === 'pickup')
                                — gratis (ambil sendiri)
                            @endif
                        </span>
                        <span>Rp {{ number_format($group['shipping']['cost'], 0, ',', '.') }}</span>
                    </div>
                    @if($group['fulfillment_type'] === 'delivery' && ! $group['shipping']['within_radius'])
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">
                            Toko di luar jangkauan pengiriman — pilih alamat lain atau ambil sendiri.
                        </p>
                    @endif
                    <div class="mt-2 flex justify-between font-semibold text-gray-900 dark:text-white">
                        <span>Total Toko</span>
                        <span>Rp {{ number_format($group['total'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h2 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">Tukar Poin</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Saldo poin: <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($available_points) }}</span> poin
            — 100 poin = Rp 1.000.
        </p>

        <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center">
            <input type="number" name="points" min="0" step="100" value="{{ old('points', 0) }}"
                   placeholder="Contoh: 100"
                   class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 sm:w-48">
            <span class="text-xs text-gray-500 dark:text-gray-400">Masukkan kelipatan 100.</span>
        </div>
        @error('points')
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-center justify-between">
            <p class="text-lg font-bold text-gray-900 dark:text-white">Grand Total</p>
            <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">Rp {{ number_format($summary['grand_total'], 0, ',', '.') }}</p>
        </div>

        <button type="submit"
                class="mt-4 w-full rounded-lg bg-indigo-600 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            Buat Pesanan
        </button>
        <p class="mt-2 text-center text-xs text-gray-500 dark:text-gray-400">Pesanan diproses setelah toko mengonfirmasi pembayaran.</p>
    </div>
</form>
@endsection
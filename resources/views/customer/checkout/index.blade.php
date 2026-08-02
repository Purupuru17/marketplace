@extends('customer.layouts.app')
@section('title', 'Checkout')

@section('content')
<h1 class="mb-8 text-2xl font-bold text-gray-900 dark:text-white">Checkout</h1>

@if($addresses->isEmpty())
    <div class="rounded-2xl border border-dashed border-gray-300 p-12 text-center text-gray-500 dark:border-gray-700 dark:text-gray-400">
        Kamu belum punya alamat pengiriman.
        <a href="{{ route('customer.address.create') }}" class="mt-4 block font-medium text-indigo-600 dark:text-indigo-400">+ Tambah Alamat</a>
    </div>
@else
    <form method="POST" action="{{ route('customer.checkout.store') }}">
        @csrf
        <input type="hidden" name="address_id" value="{{ $summary['address']?->id }}">

        <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Alamat Pengiriman</h2>
                <a href="{{ route('customer.address.index') }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Kelola Alamat</a>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @foreach($addresses as $address)
                    <a href="{{ route('customer.checkout.index', ['address_id' => $address->id]) }}"
                       class="rounded-xl border p-4 transition @if($summary['address']?->id === $address->id) border-indigo-500 bg-indigo-50/50 dark:bg-indigo-500/10 @else border-gray-200 hover:border-gray-300 dark:border-gray-700 @endif">
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
            @error('address_id')
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6 space-y-4">
            @foreach($summary['by_store'] as $group)
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
                                </div>
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    Rp {{ number_format((float) $item->variant->price * $item->qty, 0, ',', '.') }}
                                </p>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-t border-gray-100 px-6 py-4 text-sm dark:border-gray-800">
                        <div class="flex justify-between text-gray-600 dark:text-gray-300">
                            <span>Subtotal</span>
                            <span>Rp {{ number_format($group['subtotal'], 0, ',', '.') }}</span>
                        </div>
                        <div class="mt-1 flex justify-between text-gray-600 dark:text-gray-300">
                            <span>Ongkir ({{ $group['shipping']['distance_km'] !== null ? $group['shipping']['distance_km'] . ' km' : '—' }})</span>
                            <span>Rp {{ number_format($group['shipping']['cost'], 0, ',', '.') }}</span>
                        </div>
                        @if(! $group['shipping']['within_radius'])
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">
                                Toko di luar jangkauan pengiriman — pilih alamat lain.
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

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <p class="text-lg font-bold text-gray-900 dark:text-white">Grand Total</p>
                <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">Rp {{ number_format($summary['grand_total'], 0, ',', '.') }}</p>
            </div>

            <button type="submit"
                    class="mt-4 w-full rounded-lg bg-indigo-600 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Buat Pesanan
            </button>
            <p class="mt-2 text-center text-xs text-gray-500 dark:text-gray-400">Pembayaran akan dibuka setelah pesanan dibuat (fitur menyusul).</p>
        </div>
    </form>
@endif
@endsection

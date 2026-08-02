@extends('customer.layouts.app')
@section('title', 'Keranjang')

@section('content')
<h1 class="mb-8 text-2xl font-bold text-gray-900 dark:text-white">Keranjang Belanja</h1>

@if($items->isEmpty())
    <div class="rounded-2xl border border-dashed border-gray-300 p-12 text-center text-gray-500 dark:border-gray-700 dark:text-gray-400">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800">
            @svg('heroicon-o-shopping-cart', 'h-7 w-7')
        </div>
        <p class="mt-4">Keranjang masih kosong.</p>
        <a href="{{ route('storefront.index') }}"
           class="mt-4 inline-block rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
            Mulai Belanja
        </a>
    </div>
@else
    <div class="space-y-6">
        @foreach($by_store as $group)
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <a href="{{ route('storefront.store', $group['store']->slug) }}"
                       class="font-semibold text-gray-900 hover:text-indigo-600 dark:text-white dark:hover:text-indigo-400">
                        {{ $group['store']->store_name }}
                    </a>
                </div>

                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($group['items'] as $item)
                        <div class="flex flex-col gap-4 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <a href="{{ route('storefront.product', [$group['store']->slug, $item->variant->product->slug]) }}"
                                   class="font-medium text-gray-900 hover:text-indigo-600 dark:text-white dark:hover:text-indigo-400">
                                    {{ $item->variant->product->name }}
                                </a>
                                <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                    <span>{{ $item->variant->sku }}</span>
                                    @if($item->variant->attributeValues->isNotEmpty())
                                        <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                            {{ $item->variant->attributeValues->sortBy(fn ($v) => $v->attribute?->name)->pluck('value')->join(' · ') }}
                                        </span>
                                    @endif
                                </div>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Rp {{ number_format((float) $item->variant->price, 0, ',', '.') }} / pcs
                                </p>
                            </div>

                            <div class="flex items-center gap-4">
                                <form method="POST" action="{{ route('customer.cart.update', $item->id) }}" class="flex items-center gap-2">
                                    @csrf @method('PUT')
                                    <input type="number" name="qty" value="{{ $item->qty }}" min="1" max="{{ $item->variant->stock }}"
                                           class="w-20 rounded-lg border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    <button type="submit"
                                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                                        Ubah
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('customer.cart.destroy', $item->id) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="rounded-lg p-2 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10">
                                        @svg('heroicon-o-trash', 'h-5 w-5')
                                    </button>
                                </form>

                                <div class="w-28 text-right">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Subtotal</p>
                                    <p class="font-semibold text-gray-900 dark:text-white">
                                        Rp {{ number_format((float) $item->variant->price * $item->qty, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-gray-100 px-6 py-4 text-right dark:border-gray-800">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Subtotal toko: <span class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($group['subtotal'], 0, ',', '.') }}</span>
                    </p>
                </div>
            </div>
        @endforeach

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <p class="text-lg font-bold text-gray-900 dark:text-white">Total Belanja</p>
                <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">Rp {{ number_format($total, 0, ',', '.') }}</p>
            </div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Pembayaran dan pengiriman akan dihitung saat checkout (fitur menyusul).</p>
        </div>
    </div>
@endif
@endsection

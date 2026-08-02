@extends('customer.layouts.app')
@section('title', 'Favorit Saya')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Favorit Saya</h1>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Produk yang kamu simpan untuk dibeli nanti.</p>
</div>

@if($products->isEmpty())
    <div class="rounded-2xl border border-dashed border-gray-300 p-12 text-center text-gray-500 dark:border-gray-700 dark:text-gray-400">
        Belum ada produk favorit.
        <a href="{{ route('storefront.index') }}" class="mt-4 block font-medium text-indigo-600 dark:text-indigo-400">Jelajahi toko</a>
    </div>
@else
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($products as $product)
            @php
                $pricingService = app(\App\Services\Pricing\PromotionPricingService::class);
                $minPrice = $product->variants->map(fn ($v) => $pricingService->pricing($v)['effective'])->min();
                $hasPromo = $product->variants->contains(fn ($v) => $pricingService->pricing($v)['promotion'] !== null);
            @endphp
            <div class="group flex flex-col rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:border-indigo-300 hover:shadow-md dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-700">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">
                        @svg('heroicon-o-shopping-bag', 'h-6 w-6')
                    </div>
                    <div class="flex flex-wrap items-center justify-end gap-1.5">
                        @if($hasPromo)
                            <span class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-600 dark:bg-red-500/10 dark:text-red-400">Promo</span>
                        @endif
                        <form method="POST" action="{{ route('customer.favorite.toggle') }}">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <button type="submit" title="Hapus dari favorit"
                                    class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-600 transition hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20">
                                @svg('heroicon-s-heart', 'h-3.5 w-3.5') Hapus
                            </button>
                        </form>
                    </div>
                </div>

                <a href="{{ route('storefront.product', [$product->store->slug, $product->slug]) }}" class="mt-4 flex-1">
                    <h2 class="text-lg font-semibold text-gray-900 group-hover:text-indigo-600 dark:text-white dark:group-hover:text-indigo-400">
                        {{ $product->name }}
                    </h2>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $product->store->store_name }}</p>
                    <p class="mt-2 text-sm font-semibold text-indigo-600 dark:text-indigo-400">
                        @if($product->variants->isNotEmpty())
                            Rp {{ number_format($minPrice, 0, ',', '.') }}+
                        @else
                            Belum ada varian
                        @endif
                    </p>
                </a>
            </div>
        @endforeach
    </div>
@endif
@endsection

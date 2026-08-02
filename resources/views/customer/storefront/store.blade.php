@extends('customer.layouts.app')
@section('title', $store->store_name)

@section('content')
<a href="{{ route('storefront.index') }}" class="mb-6 inline-flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
    @svg('heroicon-o-arrow-left', 'h-4 w-4') Daftar Toko
</a>

<div class="mb-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex items-start gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">
                @svg('heroicon-o-building-storefront', 'h-7 w-7')
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $store->store_name }}</h1>
                @if($store->description)
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $store->description }}</p>
                @endif
                <div class="mt-3 flex flex-wrap gap-3 text-xs text-gray-500 dark:text-gray-400">
                    @if($store->locationNode)
                        <span class="inline-flex items-center gap-1">
                            @svg('heroicon-o-map-pin', 'h-3.5 w-3.5') {{ $store->locationNode->name }}
                        </span>
                    @endif
                    @if($store->phone)
                        <span class="inline-flex items-center gap-1">
                            @svg('heroicon-o-phone', 'h-3.5 w-3.5') {{ $store->phone }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<form method="GET" action="{{ route('storefront.store', $store->slug) }}" class="mb-6 flex max-w-md gap-2">
    <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari produk di toko ini..."
           class="w-full rounded-lg border-gray-300 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
    <button type="submit"
            class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">Cari</button>
</form>

@if($products->isEmpty())
    <div class="rounded-2xl border border-dashed border-gray-300 p-12 text-center text-gray-500 dark:border-gray-700 dark:text-gray-400">
        Belum ada produk aktif di toko ini.
    </div>
@else
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($products as $product)
            @php
                $pricingService = app(\App\Services\Pricing\PromotionService::class);
                $effectivePrices = $product->variants->map(fn ($v) => $pricingService->pricing($v)['effective']);
                $minPrice = $effectivePrices->min();
                $hasPromo = $product->variants->contains(fn ($v) => $pricingService->pricing($v)['promotion'] !== null);
            @endphp
            <a href="{{ route('storefront.product', [$store->slug, $product->slug]) }}"
               class="group rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:border-indigo-300 hover:shadow-md dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-700">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">
                        @svg('heroicon-o-shopping-bag', 'h-6 w-6')
                    </div>
                    <div class="flex flex-wrap items-center justify-end gap-1.5">
                        @if($hasPromo)
                            <span class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-600 dark:bg-red-500/10 dark:text-red-400">Promo</span>
                        @endif
                        @if($product->category)
                            <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">
                                {{ $product->category->name }}
                            </span>
                        @endif
                    </div>
                </div>

                <h2 class="mt-4 text-lg font-semibold text-gray-900 group-hover:text-indigo-600 dark:text-white dark:group-hover:text-indigo-400">
                    {{ $product->name }}
                </h2>

                <p class="mt-1 text-sm font-semibold text-indigo-600 dark:text-indigo-400">
                    @if($product->variants->isNotEmpty())
                        Rp {{ number_format($minPrice, 0, ',', '.') }}+ · {{ $product->variants->count() }} varian
                    @else
                        Belum ada varian
                    @endif
                </p>
            </a>
        @endforeach
    </div>

    <div class="mt-8">
        {{ $products->links() }}
    </div>
@endif
@endsection

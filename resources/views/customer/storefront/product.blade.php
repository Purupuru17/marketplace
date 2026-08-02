@extends('customer.layouts.app')
@section('title', $product->name)

@section('content')
@php
    $pricingService = app(\App\Services\Pricing\PromotionPricingService::class);
    $variantPricing = $product->variants->mapWithKeys(
        fn ($v) => [$v->id => $pricingService->pricing($v)]
    );
    $avgRating = $product->ratings->isNotEmpty() ? round($product->ratings->avg('rating'), 1) : null;
    $isFavorite = auth('customer')->check() && auth('customer')->user()->favoriteProducts()->whereKey($product->id)->exists();
@endphp
<a href="{{ route('storefront.store', $store->slug) }}"
   class="mb-6 inline-flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
    @svg('heroicon-o-arrow-left', 'h-4 w-4') {{ $store->store_name }}
</a>

<div class="mb-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex items-start gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">
                @svg('heroicon-o-shopping-bag', 'h-7 w-7')
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $product->name }}</h1>
                <div class="mt-2 flex flex-wrap items-center gap-2">
                    @if($product->category)
                        <span class="inline-block rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">
                            {{ $product->category->name }}
                        </span>
                    @endif
                    @if($avgRating !== null)
                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                            @svg('heroicon-o-star', 'h-3.5 w-3.5')
                            {{ $avgRating }} ({{ $product->ratings->count() }})
                        </span>
                    @endif
                </div>
                @if($product->description)
                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">{{ $product->description }}</p>
                @endif
            </div>
        </div>

        @auth('customer')
            <form method="POST" action="{{ route('customer.favorite.toggle') }}">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-600 transition hover:border-red-300 hover:text-red-500 dark:border-gray-700 dark:text-gray-300 dark:hover:border-red-700 dark:hover:text-red-400">
                    @if($isFavorite)
                        <span class="text-red-500">@svg('heroicon-s-heart', 'h-5 w-5')</span> Favorit
                    @else
                        @svg('heroicon-o-heart', 'h-5 w-5') Tambah Favorit
                    @endif
                </button>
            </form>

            <form method="POST" action="{{ route('customer.chat.start') }}">
                @csrf
                <input type="hidden" name="store_id" value="{{ $store->id }}">
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500">
                    @svg('heroicon-o-chat-bubble-left-right', 'h-5 w-5') Tanya Toko
                </button>
            </form>
        @endauth
    </div>
</div>

@if($product->variants->isEmpty())
    <div class="rounded-2xl border border-dashed border-gray-300 p-12 text-center text-gray-500 dark:border-gray-700 dark:text-gray-400">
        Produk ini belum memiliki varian aktif.
    </div>
@else
    <div class="space-y-4">
        @foreach($product->variants as $variant)
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $variant->sku }}</span>
                            @if($variant->attributeValues->isNotEmpty())
                                <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    {{ $variant->attributeValues->sortBy(fn ($v) => $v->attribute?->name)->pluck('value')->join(' · ') }}
                                </span>
                            @endif
                        </div>

                        <p class="mt-2 text-xl font-bold text-gray-900 dark:text-white">
                            Rp {{ number_format((float) $variantPricing[$variant->id]['effective'], 0, ',', '.') }}
                        </p>

                        @if($variantPricing[$variant->id]['discount'] > 0)
                            <p class="mt-1 flex items-center gap-2 text-sm">
                                <span class="text-gray-400 line-through dark:text-gray-500">Rp {{ number_format((float) $variantPricing[$variant->id]['original'], 0, ',', '.') }}</span>
                                <span class="rounded-full bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-600 dark:bg-red-500/10 dark:text-red-400">
                                    {{ $variantPricing[$variant->id]['promotion']->name }}
                                </span>
                            </p>
                        @endif

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            @if($variant->stock > 0)
                                Stok {{ $variant->stock }}
                            @else
                                <span class="text-red-500">Stok habis</span>
                            @endif
                        </p>
                    </div>

                    <div class="sm:w-64">
                        @if($variant->stock > 0)
                            @auth('customer')
                                <form method="POST" action="{{ route('customer.cart.store') }}" class="flex items-center gap-2">
                                    @csrf
                                    <input type="hidden" name="variant_id" value="{{ $variant->id }}">
                                    <input type="number" name="qty" value="1" min="1" max="{{ $variant->stock }}"
                                           class="w-20 rounded-lg border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    <button type="submit"
                                            class="flex-1 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                                        + Keranjang
                                    </button>
                                </form>
                                @error('qty')
                                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                                @error('variant_id')
                                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            @else
                                <a href="{{ route('customer.auth.login') }}"
                                   class="block rounded-lg border border-indigo-600 px-4 py-2 text-center text-sm font-semibold text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-500/10">
                                    Masuk untuk membeli
                                </a>
                            @endauth
                        @else
                            <span class="block rounded-lg bg-gray-100 px-4 py-2 text-center text-sm font-semibold text-gray-400 dark:bg-gray-800">Stok habis</span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

@if($product->ratings->isNotEmpty())
    <div class="mt-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Ulasan Pembeli</h2>
        <div class="mt-4 space-y-4">
            @foreach($product->ratings as $rating)
                <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/30">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $rating->customer?->name ?? 'Customer' }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $rating->created_at->format('d M Y') }}</p>
                        </div>
                        <div class="flex items-center gap-0.5 text-amber-500">
                            @for($i = 1; $i <= 5; $i++)
                                @svg($i <= $rating->rating ? 'heroicon-s-star' : 'heroicon-o-star', 'h-4 w-4')
                            @endfor
                        </div>
                    </div>
                    @if($rating->review)
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $rating->review }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif
@endsection

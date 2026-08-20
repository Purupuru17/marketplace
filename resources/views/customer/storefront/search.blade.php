@extends('customer.layouts.app')
@section('title', 'Pencarian')

@section('content')
@php
    $q = $filters['q'] ?? '';
    $categoryIds = (array) ($filters['category_ids'] ?? []);
    $radius = $filters['radius'] ?? '';
    $activeFilterCount = count($categoryIds) + ($radius ? 1 : 0) + (($filters['min_price'] ?? '') ? 1 : 0) + (($filters['max_price'] ?? '') ? 1 : 0);
    $customerCartCount = auth('customer')->check() ? app(\App\Services\Customer\CartService::class)->count(auth('customer')->user()) : 0;
    $queryParams = collect($filters)->except(['store_ids'])->all();
@endphp

<div x-data="{ filterOpen: false, view: 'grid', radius: {{ $radius ?: 10 }} }">
<form method="GET" action="{{ route('storefront.search') }}">
<header class="sticky top-0 z-30 bg-white border-b border-gray-100 px-4 py-3">
    <div class="flex items-center gap-2">
        <a href="{{ url()->previous() && ! request()->routeIs('storefront.search') ? url()->previous() : route('storefront.index') }}" class="w-8 h-8 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div class="flex-1 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0a7.5 7.5 0 10-10.6 0 7.5 7.5 0 0010.6 0z"/></svg>
            <input name="q" value="{{ $q }}" placeholder="Cari produk atau toko..." autocomplete="off"
                   class="w-full bg-gray-100 border-0 rounded-full pl-9 pr-3 py-2 text-sm text-gray-800 focus:ring-0">
        </div>
        <button type="button" @click="filterOpen = true" class="relative w-9 h-9 flex items-center justify-center rounded-full {{ $activeFilterCount > 0 ? 'bg-emerald-700 text-white' : 'bg-gray-100 text-gray-600' }} shrink-0">
            <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            @if($activeFilterCount > 0)
                <span class="absolute -top-1 -right-1 bg-red-600 text-white text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center">{{ $activeFilterCount }}</span>
            @endif
        </button>
    </div>

    @if($activeFilterCount > 0)
        <div class="flex gap-2 overflow-x-auto no-scrollbar mt-2.5">
            @foreach($categoryIds as $catId)
                @php $cat = $categories->firstWhere('id', $catId); @endphp
                @if($cat)
                    <a href="{{ url()->current() . '?' . http_build_query(array_merge($queryParams, ['category_ids' => array_values(array_diff($categoryIds, [$catId]))])) }}"
                       class="shrink-0 inline-flex items-center gap-1.5 text-xs font-medium bg-emerald-50 text-emerald-800 border border-emerald-100 rounded-full pl-3 pr-2 py-1.5">
                        {{ $cat->name }}
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            @endforeach
            @if($radius)
                <a href="{{ url()->current() . '?' . http_build_query(array_merge($queryParams, ['radius' => null])) }}"
                   class="shrink-0 inline-flex items-center gap-1.5 text-xs font-medium bg-emerald-50 text-emerald-800 border border-emerald-100 rounded-full pl-3 pr-2 py-1.5">
                    Radius {{ $radius }}km
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            @endif
        </div>
    @endif
</header>

<main class="px-4">

    <div class="flex items-center justify-between mt-3">
        <span class="text-xs text-gray-500">
            {{ $products->total() }} produk {!! $q ? 'untuk <span class="font-semibold text-gray-800">"' . e($q) . '"</span>' : '' !!}
        </span>
        <div class="flex items-center gap-2">
            <button type="button" @click="filterOpen = true" class="flex items-center gap-1 text-xs font-medium text-gray-700 border border-gray-200 rounded-lg px-2.5 py-1.5">
                {{ ['default' => 'Urutkan', 'price_asc' => 'Termurah', 'price_desc' => 'Termahal', 'latest' => 'Terbaru', 'sold' => 'Terlaris'][$filters['sort'] ?? 'default'] }}
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <button type="button" @click="view = view === 'grid' ? 'list' : 'grid'"
                    class="w-7 h-7 flex items-center justify-center border border-gray-200 rounded-lg">
                <template x-if="view === 'grid'">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                </template>
                <template x-if="view === 'list'">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </template>
            </button>
        </div>
    </div>

    @if($products->isEmpty())
        <div class="flex flex-col items-center justify-center text-center px-8 py-20">
            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                <svg class="w-7 h-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0a7.5 7.5 0 10-10.6 0 7.5 7.5 0 0010.6 0z"/></svg>
            </div>
            <p class="text-sm font-semibold text-gray-800">Tidak ditemukan produk {!! $q ? 'untuk "' . e($q) . '"' : '' !!}</p>
            <p class="text-xs text-gray-500 mt-1.5">Coba perluas radius pencarian atau gunakan kata kunci lain</p>
            <a href="{{ url()->current() . '?' . http_build_query(array_merge($queryParams, ['radius' => '10', 'q' => $q])) }}"
               class="mt-4 text-xs font-semibold text-emerald-700 border border-emerald-700 rounded-lg px-4 py-2">Perluas Radius ke 10km</a>
        </div>
    @else
        <div x-show="view === 'grid'" x-cloak class="grid grid-cols-2 gap-3 mt-3">
            @foreach($products as $product)
                @include('customer.storefront.partials.product-card', ['product' => $product, 'showStore' => true])
            @endforeach
        </div>

        <div x-show="view === 'list'" x-cloak class="space-y-3 mt-3">
            @foreach($products as $product)
                <a href="{{ route('storefront.product', [$product->store->slug, $product->slug]) }}" class="flex gap-3 bg-white rounded-xl border border-gray-100 p-2.5">
                    <div class="w-20 h-20 rounded-lg bg-gray-100 shrink-0 overflow-hidden">
                        @if($product->primary_image)
                            <img src="{{ asset('storage/' . $product->primary_image) }}" alt="" class="w-full h-full object-cover">
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] leading-tight line-clamp-2 text-gray-900">{{ $product->name }}</p>
                        <p class="text-[11px] text-gray-500 truncate mt-0.5">{{ $product->store->store_name }}</p>
                        <div class="flex items-baseline gap-1.5 mt-1">
                            @if($product->discount_percent > 0)
                                <span class="text-[10px] text-gray-400 line-through">Rp {{ number_format((float) $product->min_original_price, 0, ',', '.') }}</span>
                            @endif
                            <span class="text-sm font-bold text-emerald-700">Rp {{ number_format((float) $product->min_price, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $products->withQueryString()->links() }}
        </div>
    @endif

</main>

<!-- Bottom Sheet Filter -->
<div x-show="filterOpen" x-cloak class="fixed inset-0 z-50" @keydown.escape.window="filterOpen = false">
    <div class="absolute inset-0 bg-black/40" @click="filterOpen = false"></div>
    <div class="absolute inset-x-0 bottom-0 max-w-[420px] mx-auto bg-white rounded-t-2xl max-h-[85%] flex flex-col">
        <div class="flex items-center justify-between px-4 pt-4 pb-3 border-b border-gray-100 shrink-0">
            <button type="submit" form="search-form" @click="filterOpen = false" class="text-xs font-medium text-gray-500">Tutup</button>
            <h2 class="font-bold text-[15px] text-gray-900">Filter</h2>
            <button type="button" @click="filterOpen = false" class="w-7 h-7 flex items-center justify-center">
                <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="px-4 py-4 overflow-y-auto space-y-6">
            <p class="text-xs text-gray-400 text-right">
                <a href="{{ route('storefront.search') }}" class="font-semibold text-emerald-700">Reset</a>
            </p>

            <div>
                <p class="text-[13px] font-semibold text-gray-800 mb-2.5">Kategori</p>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($categories as $category)
                        <label class="flex items-center gap-2 text-[13px] text-gray-700">
                            <input type="checkbox" name="category_ids[]" value="{{ $category->id }}"
                                   @checked(in_array($category->id, $categoryIds))
                                   class="w-4 h-4 rounded border-gray-300 text-emerald-700 focus:ring-emerald-600">
                            {{ $category->name }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <p class="text-[13px] font-semibold text-gray-800 mb-2.5">Rentang Harga</p>
                <div class="flex items-center gap-2">
                    <input type="number" name="min_price" value="{{ $filters['min_price'] ?? '' }}" min="0" placeholder="Min (Rp)"
                           class="w-full text-xs text-gray-700 border border-gray-200 rounded-lg px-2.5 py-2">
                    <span class="text-gray-400 text-xs">—</span>
                    <input type="number" name="max_price" value="{{ $filters['max_price'] ?? '' }}" min="0" placeholder="Max (Rp)"
                           class="w-full text-xs text-gray-700 border border-gray-200 rounded-lg px-2.5 py-2">
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2.5">
                    <p class="text-[13px] font-semibold text-gray-800">Radius Toko</p>
                    <span class="text-xs font-semibold text-emerald-700" x-text="radius + ' km'">10 km</span>
                </div>
                <input type="range" name="radius" min="1" max="10" step="1" x-model="radius"
                       class="w-full accent-emerald-700">
            </div>

            <div>
                <p class="text-[13px] font-semibold text-gray-800 mb-2.5">Urutkan</p>
                <div class="flex flex-wrap gap-2">
                    @foreach(['default' => 'Paling Relevan', 'price_asc' => 'Termurah', 'price_desc' => 'Termahal', 'latest' => 'Terbaru', 'sold' => 'Terlaris'] as $key => $label)
                        <label class="cursor-pointer">
                            <input type="radio" name="sort" value="{{ $key }}" @checked(($filters['sort'] ?? 'default') === $key) class="peer sr-only">
                            <span class="inline-block text-xs font-medium rounded-full px-3.5 py-2 bg-white border border-gray-200 text-gray-700 peer-checked:bg-emerald-700 peer-checked:text-white peer-checked:border-emerald-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="px-4 py-3 border-t border-gray-100 shrink-0">
            <button type="submit" @click="filterOpen = false"
                    class="w-full text-sm font-semibold text-white bg-emerald-700 rounded-lg py-3">Terapkan Filter</button>
        </div>
    </div>
</div>
</form>
</div>
@endsection
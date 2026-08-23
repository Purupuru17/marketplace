@extends('customer.layouts.app')
@section('title', 'Semua Toko')

@section('content')
<header class="sticky top-0 z-30 bg-white border-b border-gray-100 px-4 py-3">
    <div class="flex items-center gap-2 mb-2.5">
        <a href="{{ route('storefront.index') }}" class="w-8 h-8 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="font-bold text-[15px] text-gray-900">Semua Toko</h1>
    </div>
    <form method="GET" action="{{ route('storefront.stores') }}" class="flex items-center gap-2">
        <div class="flex-1 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0a7.5 7.5 0 10-10.6 0 7.5 7.5 0 0010.6 0z"/></svg>
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari nama toko..." class="w-full bg-gray-100 border-0 rounded-full pl-9 pr-3 py-2 text-sm text-gray-800 focus:ring-0">
        </div>
        <button type="submit" class="shrink-0 w-9 h-9 flex items-center justify-center rounded-full bg-emerald-700 text-white">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0a7.5 7.5 0 10-10.6 0 7.5 7.5 0 0010.6 0z"/></svg>
        </button>
    </form>
    <div class="flex gap-2 overflow-x-auto no-scrollbar mt-2.5">
        @php $currentStatus = $filters['status'] ?? 'all'; @endphp
        <a href="{{ route('storefront.stores', array_merge($filters, ['status' => 'all'])) }}" class="shrink-0 text-xs font-medium rounded-full px-3.5 py-1.5 {{ $currentStatus === 'all' ? 'bg-emerald-700 text-white font-semibold' : 'bg-white border border-gray-200 text-gray-600' }}">Semua</a>
        <a href="{{ route('storefront.stores', array_merge($filters, ['status' => 'open'])) }}" class="shrink-0 text-xs font-medium rounded-full px-3.5 py-1.5 {{ $currentStatus === 'open' ? 'bg-emerald-700 text-white font-semibold' : 'bg-white border border-gray-200 text-gray-600' }}">Buka Sekarang</a>
        <a href="{{ route('storefront.stores', array_merge($filters, ['status' => 'premium'])) }}" class="shrink-0 text-xs font-medium rounded-full px-3.5 py-1.5 {{ $currentStatus === 'premium' ? 'bg-emerald-700 text-white font-semibold' : 'bg-white border border-gray-200 text-gray-600' }}">Toko Premium</a>
    </div>
</header>

<main class="px-4">
    <div class="flex items-center justify-between mt-3">
        <span class="text-[11px] text-gray-500">{{ $stores->total() }} toko di sekitarmu</span>
        @php $currentSort = $filters['sort'] ?? 'default'; @endphp
        <div class="flex items-center gap-1.5">
            <a href="{{ route('storefront.stores', array_merge($filters, ['sort' => $currentSort === 'nearest' ? 'default' : 'nearest'])) }}" class="flex items-center gap-1 text-xs font-medium {{ $currentSort === 'nearest' ? 'text-emerald-700 border-emerald-200 bg-emerald-50' : 'text-gray-700 border-gray-200 bg-white' }} border rounded-lg px-2.5 py-1.5">
                Terdekat
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </a>
        </div>
    </div>

    @forelse($stores as $store)
        @php
            $rating = $ratingMap[$store->id] ?? ['avg_rating' => null, 'rating_count' => 0, 'product_count' => 0];
            $open = $store->open_status['open'] ?? false;
        @endphp
        <a href="{{ route('storefront.store', $store->slug) }}" class="mt-3 bg-white rounded-xl border border-gray-100 p-3.5 block {{ $open ? '' : 'opacity-70' }}">
            <div class="flex items-start gap-3">
                <div class="w-14 h-14 rounded-xl {{ $open ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-500' }} flex items-center justify-center font-bold text-base shrink-0">{{ strtoupper(mb_substr($store->store_name, 0, 2)) }}</div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1">
                        <p class="text-sm font-bold text-gray-900 truncate">{{ $store->store_name }}</p>
                        @if($store->level && str_contains(strtolower($store->level->name), 'premium'))
                            <span class="text-amber-500 text-xs shrink-0">👑</span>
                        @endif
                    </div>
                    <p class="text-[11px] text-gray-500 mt-0.5">
                        @if($rating['avg_rating']) ⭐ {{ number_format($rating['avg_rating'], 1) }} ({{ $rating['rating_count'] }} ulasan) · @endif
                        {{ $rating['product_count'] }} produk
                    </p>
                    <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                        @if($open)
                            <span class="inline-flex items-center gap-1 text-[10px] font-medium bg-emerald-50 text-emerald-700 rounded-full px-2 py-0.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Buka
                                @if(! empty($store->open_status['label'])) · {{ $store->open_status['label'] }} @endif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-[10px] font-medium bg-red-50 text-red-600 rounded-full px-2 py-0.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Tutup
                                @if(! empty($store->open_status['label'])) · Buka jam {{ $store->open_status['label'] }} @endif
                            </span>
                        @endif
                        @if($store->locationNode)
                            <span class="inline-flex items-center gap-1 text-[10px] font-medium bg-gray-50 text-gray-600 rounded-full px-2 py-0.5">
                                <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ $store->locationNode->name }}
                                @if($store->distance_km !== null) · {{ number_format($store->distance_km, 1, ',', '.') }} km @endif
                                · Rp{{ number_format((float) $store->rate_per_km * 1000, 0, ',', '.') }}/km
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </a>
    @empty
        <div class="mt-6 flex flex-col items-center justify-center text-center px-8 py-12">
            <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <p class="text-sm font-semibold text-gray-800">Belum ada toko</p>
            <p class="text-xs text-gray-500 mt-1">Coba ubah filter atau kata kunci pencarian</p>
        </div>
    @endforelse

    <div class="mt-4">
        {{ $stores->withQueryString()->links() }}
    </div>
</main>
@endsection

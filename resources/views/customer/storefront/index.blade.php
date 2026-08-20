@extends('customer.layouts.app')
@section('title', 'Beranda')

@section('content')
@php
    $customerCartCount = auth('customer')->check() ? app(\App\Services\Customer\CartService::class)->count(auth('customer')->user()) : 0;
@endphp
<header class="sticky top-0 z-30 bg-white border-b border-gray-100">
    <div class="px-4 pt-3 pb-2 flex items-center gap-2">
        <a href="{{ route('storefront.index') }}" class="font-extrabold text-lg text-emerald-800 shrink-0">
            {{ config('app.name') }}
        </a>
        <a href="{{ route('storefront.search') }}" class="flex-1 relative block">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0a7.5 7.5 0 10-10.6 0 7.5 7.5 0 0010.6 0z"/></svg>
            <span class="block w-full bg-gray-100 border-0 rounded-full pl-9 pr-3 py-2 text-sm text-gray-500">Cari produk atau toko...</span>
        </a>
        <a href="{{ route('customer.cart.index') }}" class="relative w-9 h-9 flex items-center justify-center rounded-full bg-gray-100 shrink-0">
            <svg class="w-[18px] h-[18px] text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            @if($customerCartCount > 0)
                <span class="absolute -top-0.5 -right-0.5 bg-emerald-600 text-white text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center">{{ $customerCartCount }}</span>
            @endif
        </a>
    </div>
    <a href="{{ auth('customer')->check() ? route('customer.address.index') : route('customer.auth.login') }}"
       class="mx-4 mb-2.5 flex items-center gap-1 text-xs text-gray-600">
        <svg class="w-3.5 h-3.5 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        <span class="font-medium truncate">
            @if($address_label)
                {{ $address_label }}
            @elseif(auth('customer')->check())
                Alamat belum diatur
            @else
                Atur alamat pengiriman
            @endif
        </span>
        <svg class="w-3 h-3 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
    </a>
</header>

<main class="px-4">

    <section class="mt-3">
        <div class="relative rounded-2xl bg-gradient-to-br from-emerald-700 to-emerald-800 text-white p-4 h-32 flex flex-col justify-between overflow-hidden">
            <div class="absolute -right-6 -bottom-8 w-32 h-32 rounded-full bg-white/10"></div>
            <div class="relative">
                <p class="text-xs font-medium text-emerald-100">Promo Akhir Pekan</p>
                <p class="text-lg font-bold leading-snug mt-0.5">Diskon s.d. 20%<br>Sembako Pilihan</p>
            </div>
            <a href="{{ route('storefront.search') }}" class="relative inline-flex items-center gap-1 bg-white/15 backdrop-blur text-[11px] font-medium rounded-full px-2.5 py-1 w-fit">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Lihat promonya
            </a>
        </div>
    </section>

    @if($categories->isNotEmpty())
        <section class="mt-5">
            <div class="flex gap-2 overflow-x-auto no-scrollbar">
                <a href="{{ route('storefront.search') }}" class="shrink-0 text-xs font-semibold bg-emerald-700 text-white rounded-full px-3.5 py-1.5">Semua</a>
                @foreach($categories as $category)
                    <a href="{{ route('storefront.search', ['category_id' => $category->id]) }}"
                       class="shrink-0 text-xs font-medium bg-white border border-gray-200 text-gray-600 rounded-full px-3.5 py-1.5">{{ $category->name }}</a>
                @endforeach
            </div>
        </section>
    @endif

    @if($nearby_stores->isNotEmpty())
        <section class="mt-6">
            <div class="flex items-center justify-between mb-2.5">
                <h2 class="font-bold text-[15px] text-gray-900">Toko Terdekat</h2>
                <a href="{{ route('storefront.search') }}" class="text-xs font-semibold text-emerald-700">Lihat semua</a>
            </div>
            <div class="flex gap-3 overflow-x-auto no-scrollbar pb-1">
                @foreach($nearby_stores as $s)
                    <a href="{{ route('storefront.store', $s->slug) }}" class="shrink-0 w-52 bg-white rounded-xl border border-gray-100 p-3 {{ $s->open_status['open'] ? '' : 'opacity-60' }}">
                        <div class="flex items-center gap-2">
                            <div class="w-10 h-10 rounded-full {{ $s->open_status['open'] ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-500' }} flex items-center justify-center font-bold text-sm shrink-0">
                                {{ strtoupper(substr($s->store_name, 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold truncate flex items-center gap-1 text-gray-900">{{ $s->store_name }}</p>
                                <p class="text-[11px] {{ $s->open_status['open'] ? 'text-emerald-700' : 'text-gray-400' }} font-medium">● {{ $s->open_status['open'] ? 'Buka' : 'Tutup' }}</p>
                            </div>
                        </div>
                        <div class="mt-2.5 inline-flex items-center gap-1 text-[11px] font-medium bg-emerald-50 text-emerald-800 rounded-full px-2 py-1">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ $s->locationNode?->name ?? '—' }} (Rp{{ number_format((float) $s->rate_per_km * 1000, 0, ',', '.') }}/km)
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="mt-6">
        <div class="flex items-center justify-between mb-2.5">
            <h2 class="font-bold text-[15px] text-gray-900">Lagi Diskon</h2>
            <a href="{{ route('storefront.search') }}" class="text-xs font-semibold text-emerald-700">Lihat semua</a>
        </div>
        <div class="flex gap-3 overflow-x-auto no-scrollbar pb-1">
            @forelse($discount_products as $product)
                <div class="shrink-0 w-36">
                    @include('customer.storefront.partials.product-card', ['product' => $product, 'showStore' => true])
                </div>
            @empty
                <p class="text-xs text-gray-400 py-4">Belum ada produk diskon.</p>
            @endforelse
        </div>
    </section>

    <section class="mt-6">
        <div class="flex items-center justify-between mb-2.5">
            <h2 class="font-bold text-[15px] text-gray-900">Produk Terlaris</h2>
            <a href="{{ route('storefront.search') }}" class="text-xs font-semibold text-emerald-700">Lihat semua</a>
        </div>
        <div class="flex gap-3 overflow-x-auto no-scrollbar pb-1">
            @forelse($top_products as $product)
                <div class="shrink-0 w-36">
                    @include('customer.storefront.partials.product-card', ['product' => $product, 'showStore' => true])
                </div>
            @empty
                <p class="text-xs text-gray-400 py-4">Belum ada produk terlaris.</p>
            @endforelse
        </div>
    </section>

    @if($new_stores->isNotEmpty())
        <section class="mt-6">
            <h2 class="font-bold text-[15px] text-gray-900 mb-2.5">Toko Baru Gabung</h2>
            <div class="flex gap-3 overflow-x-auto no-scrollbar pb-1">
                @foreach($new_stores as $s)
                    <a href="{{ route('storefront.store', $s->slug) }}" class="shrink-0 w-40 bg-white rounded-xl border border-gray-100 p-3 text-center">
                        <div class="w-11 h-11 mx-auto rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-sm mb-1.5">
                            {{ strtoupper(substr($s->store_name, 0, 2)) }}
                        </div>
                        <p class="text-xs font-semibold truncate text-gray-900">{{ $s->store_name }}</p>
                        <span class="inline-block mt-1 text-[10px] bg-emerald-50 text-emerald-700 font-medium rounded-full px-2 py-0.5">Baru bergabung</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="mt-6">
        <div class="flex items-center justify-between mb-2.5">
            <h2 class="font-bold text-[15px] text-gray-900">Jelajahi Produk</h2>
        </div>
        @if($explore_products->isEmpty())
            <div class="rounded-2xl border border-dashed border-gray-300 p-12 text-center text-gray-500">
                Belum ada produk tersedia.
            </div>
        @else
            <div class="grid grid-cols-2 gap-3">
                @foreach($explore_products as $product)
                    @include('customer.storefront.partials.product-card', ['product' => $product, 'showStore' => true])
                @endforeach
            </div>
            <div class="mt-6">
                {{ $explore_products->links() }}
            </div>
        @endif
    </section>

</main>
@endsection
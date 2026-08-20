@extends('customer.layouts.app')
@section('title', $store->store_name)

@section('content')
@php
    $discountProducts = $products instanceof \Illuminate\Pagination\AbstractPaginator
        ? $products->getCollection()->filter(fn ($p) => $p->discount_percent > 0)->take(8)
        : $products->filter(fn ($p) => $p->discount_percent > 0)->take(8);
    $sortCurrent = request('sort', 'default');
    $selectedCategory = request('category_id');
@endphp

<div class="pb-20" x-data="{ tab: 'products', hoursOpen: false }">

    <header class="absolute top-0 inset-x-0 z-30 px-4 pt-3 flex items-center justify-between">
        <a href="{{ url()->previous() && ! request()->routeIs('storefront.store') ? url()->previous() : route('storefront.index') }}"
           class="w-9 h-9 flex items-center justify-center rounded-full bg-white/90 backdrop-blur shadow-sm">
            <svg class="w-5 h-5 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <button class="w-9 h-9 flex items-center justify-center rounded-full bg-white/90 backdrop-blur shadow-sm" onclick="navigator.share? navigator.share({title: document.title, url: location.href}) : null">
            <svg class="w-[18px] h-[18px] text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342a3 3 0 100-2.684m0 2.684a3 3 0 100 2.684m0-2.684l6.632 3.316m0-6l-6.632 3.316m6.632-3.316a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 6a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"/></svg>
        </button>
    </header>

    <section class="relative">
        <div class="h-32 bg-gradient-to-br from-emerald-700 to-emerald-800"></div>
        <div class="px-4">
            <div class="-mt-8 flex items-end gap-3">
                <div class="w-16 h-16 rounded-2xl bg-white border-4 border-white shadow-sm flex items-center justify-center font-bold text-emerald-800 text-lg shrink-0">
                    {{ strtoupper(substr($store->store_name, 0, 2)) }}
                </div>
                <span class="mb-1 inline-flex items-center gap-1 text-[11px] font-medium rounded-full px-2 py-0.5 
                    {{ $store->open_status['open'] ? 'text-emerald-700 bg-emerald-50 border border-emerald-100' : 'text-gray-600 bg-gray-100 border border-gray-200' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $store->open_status['open'] ? 'bg-emerald-600' : 'bg-gray-400' }}"></span>
                    {{ $store->open_status['open'] ? 'Buka' : 'Tutup' }}
                </span>
            </div>

            <div class="mt-2.5">
                <p class="text-[17px] font-bold text-gray-900 leading-tight">{{ $store->store_name }}</p>
                @if($store->level)
                    <p class="text-xs text-gray-500 font-medium mt-0.5">{{ $store->level->name }}</p>
                @endif
                <p class="text-xs text-gray-500 mt-1">
                    @if($store->avg_rating)
                        ★ <span class="font-semibold text-gray-800">{{ number_format($store->avg_rating, 1) }}</span> ({{ $store->rating_count }} ulasan) ·
                    @endif
                    Bergabung sejak {{ $store->created_at->format('M Y') }}
                </p>

                <button type="button" @click="hoursOpen = !hoursOpen" class="mt-2.5 inline-flex items-center gap-1 text-[11px] font-medium bg-emerald-50 text-emerald-800 rounded-full px-2.5 py-1.5">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $store->open_status['label'] ? 'Jam buka ' . $store->open_status['label'] : 'Jam operasional' }}
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>

                @if($store->locationNode)
                    <div class="mt-2 inline-flex items-center gap-1 text-[11px] font-medium bg-white border border-gray-200 text-gray-700 rounded-full px-2.5 py-1.5">
                        <svg class="w-3 h-3 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        {{ $store->locationNode->name }}
                    </div>
                @endif
            </div>
        </div>
    </section>

    <main class="px-4">

        <section class="mt-4 bg-white rounded-xl border border-gray-100 p-3.5">
            <p class="text-xs font-semibold text-gray-800 mb-1">Estimasi Ongkir ke Alamatmu</p>
            @if($shipping['distance_km'] !== null)
                <p class="text-[13px] text-gray-600">
                    Rp{{ number_format((float) $store->rate_per_km * 1000, 0, ',', '.') }}/km × {{ number_format($shipping['distance_km'], 1, ',', '.') }} km
                    @if($address_label)<span class="text-gray-400">dari {{ $address_label }}</span>@endif
                </p>
                <p class="text-sm font-bold text-emerald-700 mt-1">= Rp{{ number_format($shipping['cost'], 0, ',', '.') }}</p>
            @else
                <p class="text-[13px] text-gray-600">
                    <a href="{{ route('customer.address.index') }}" class="text-emerald-700 font-medium">Atur alamat</a> untuk estimasi ongkir.
                </p>
            @endif
        </section>

        <section class="mt-3 flex items-center gap-2.5">
            @auth('customer')
                <form method="POST" action="{{ route('customer.chat.start') }}" class="flex-1">
                    @csrf
                    <input type="hidden" name="store_id" value="{{ $store->id }}">
                    <button type="submit" class="w-full flex items-center justify-center gap-1.5 text-xs font-semibold text-emerald-700 border border-emerald-700 rounded-lg py-2.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        Chat Toko
                    </button>
                </form>
            @else
                <a href="{{ route('customer.auth.login') }}" class="flex-1 text-center text-xs font-semibold text-emerald-700 border border-emerald-700 rounded-lg py-2.5">Chat Toko</a>
            @endauth
            @if($store->phone)
                <a href="tel:{{ $store->phone }}" class="w-11 h-11 shrink-0 flex items-center justify-center rounded-lg border border-gray-200 text-gray-600">
                    <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                </a>
            @endif
        </section>

        @if(! $store->open_status['open'])
            <section class="mt-3 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2.5 text-xs text-amber-800">
                Toko sedang tutup. Tetap bisa lihat & simpan produk untuk nanti.
            </section>
        @endif

        @if($discountProducts->isNotEmpty())
            <section class="mt-5">
                <h2 class="font-bold text-[15px] text-gray-900 mb-2.5">Sedang Diskon</h2>
                <div class="flex gap-3 overflow-x-auto no-scrollbar pb-1">
                    @foreach($discountProducts as $product)
                        <div class="shrink-0 w-36">
                            @include('customer.storefront.partials.product-card', ['product' => $product, 'showStore' => false])
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="mt-1">
            <div class="flex border-b border-gray-200">
                <button type="button" @click="tab = 'products'" x-bind:class="tab === 'products' ? 'text-emerald-700 border-emerald-700 font-semibold' : 'text-gray-500 font-medium'"
                        class="flex-1 text-[13px] border-b-2 border-transparent py-2.5">Semua Produk</button>
                <button type="button" @click="tab = 'reviews'" x-bind:class="tab === 'reviews' ? 'text-emerald-700 border-emerald-700 font-semibold' : 'text-gray-500 font-medium'"
                        class="flex-1 text-[13px] border-b-2 border-transparent py-2.5">Ulasan</button>
            </div>

            <div x-show="tab === 'products'" x-cloak>
                <div class="mt-3">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0a7.5 7.5 0 10-10.6 0 7.5 7.5 0 0010.6 0z"/></svg>
                        <input name="q" value="{{ request('search') }}" placeholder="Cari produk di toko ini..." autocomplete="off"
                               class="w-full bg-white border border-gray-200 rounded-full pl-9 pr-3 py-2 text-sm text-gray-800 focus:ring-0"
                               onkeydown="if(event.key==='Enter'){event.preventDefault(); const u=new URL(location.href); u.searchParams.set('search',this.value); location.href=u;}">
                    </div>

                    <div class="flex gap-2 overflow-x-auto no-scrollbar mt-3">
                        <a href="{{ url()->current() . '?' . http_build_query(array_merge(request()->except(['category_id', 'page']), [])) }}" 
                           class="shrink-0 text-xs font-semibold {{ $selectedCategory ? 'bg-white border border-gray-200 text-gray-600' : 'bg-emerald-700 text-white' }} rounded-full px-3.5 py-1.5">Semua</a>
                        @foreach($categories as $category)
                            <a href="{{ url()->current() . '?' . http_build_query(array_merge(request()->except(['category_id', 'page']), ['category_id' => $category->id])) }}"
                               class="shrink-0 text-xs font-medium {{ $selectedCategory === $category->id ? 'bg-emerald-700 text-white' : 'bg-white border border-gray-200 text-gray-600' }} rounded-full px-3.5 py-1.5">{{ $category->name }}</a>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-between mt-3">
                        <span class="text-[11px] text-gray-500">{{ $products->total() }} produk</span>
                        <select onchange="this.form.submit()" name="sort" form="store-filter-form"
                                class="text-xs font-medium text-gray-700 border border-gray-200 rounded-lg px-2.5 py-1.5 bg-white">
                            <option value="default" @selected($sortCurrent === 'default')>Urutkan</option>
                            <option value="price_asc" @selected($sortCurrent === 'price_asc')>Termurah</option>
                            <option value="price_desc" @selected($sortCurrent === 'price_desc')>Termahal</option>
                            <option value="latest" @selected($sortCurrent === 'latest')>Terbaru</option>
                        </select>
                    </div>

                    <form id="store-filter-form" method="GET" action="{{ route('storefront.store', $store->slug) }}" class="hidden">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    </form>

                    @if($products->isEmpty())
                        <div class="flex flex-col items-center justify-center text-center px-8 py-16 mt-3">
                            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                                <svg class="w-7 h-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0a7.5 7.5 0 10-10.6 0 7.5 7.5 0 0010.6 0z"/></svg>
                            </div>
                            <p class="text-sm font-semibold text-gray-800">Tidak ditemukan produk di toko ini</p>
                            <p class="text-xs text-gray-500 mt-1.5">Coba kata kunci lain atau hapus filter</p>
                        </div>
                    @else
                        <div class="grid grid-cols-2 gap-3 mt-3">
                            @foreach($products as $product)
                                @include('customer.storefront.partials.product-card', ['product' => $product, 'showStore' => false])
                            @endforeach
                        </div>
                        <div class="mt-6">
                            {{ $products->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <div x-show="tab === 'reviews'" x-cloak class="mt-4 pb-4">
                @if($store_ratings->isEmpty())
                    <p class="text-xs text-gray-500 text-center py-10">Belum ada ulasan untuk toko ini.</p>
                @else
                    <div class="bg-white rounded-xl border border-gray-100 p-4">
                        @foreach($store_ratings as $rating)
                            <div class="flex gap-2.5 py-2 {{ ! $loop->first ? 'border-t border-gray-50 pt-3' : '' }}">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ strtoupper(substr($rating->customer?->name ?? 'P', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold text-gray-800">
                                        {{ $rating->customer?->name ?? 'Pembeli' }}
                                        <span class="text-amber-500 font-normal ml-1">{{ str_repeat('★', $rating->rating) }}</span>
                                    </p>
                                    @if($rating->review)
                                        <p class="text-xs text-gray-600 mt-0.5">{{ $rating->review }}</p>
                                    @endif
                                    <p class="text-[10px] text-gray-400 mt-1">{{ $rating->created_at->diffForHumans() }} · {{ $rating->product?->name }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

    </main>
</div>
@endsection
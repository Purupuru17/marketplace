@extends('customer.layouts.app')
@section('title', 'Kategori')

@section('content')
<header class="sticky top-0 z-30 bg-white border-b border-gray-100 px-4 py-3">
    <h1 class="font-bold text-[15px] text-gray-900 mb-2.5">Kategori</h1>
    <a href="{{ route('storefront.search') }}" class="w-full flex items-center gap-2 bg-gray-100 rounded-full px-3.5 py-2.5">
        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0a7.5 7.5 0 10-10.6 0 7.5 7.5 0 0010.6 0z"/></svg>
        <span class="text-sm text-gray-400">Cari produk, toko, atau kategori...</span>
    </a>
</header>

<main class="px-4">
    @if($featured->isNotEmpty())
        <section class="mt-4">
            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2.5">Sering Dicari</h2>
            <div class="flex gap-2.5 overflow-x-auto no-scrollbar">
                @foreach($featured as $cat)
                    <a href="{{ route('storefront.search', ['category_ids' => [$cat->id]]) }}" class="shrink-0 flex flex-col items-center gap-2">
                        <div class="w-16 h-16 rounded-2xl bg-emerald-50 flex items-center justify-center font-bold text-sm text-emerald-800">{{ strtoupper(mb_substr($cat->name, 0, 2)) }}</div>
                        <span class="text-[11px] font-medium text-gray-700">{{ $cat->name }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="mt-6">
        <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2.5">Semua Kategori</h2>
        <div class="grid grid-cols-4 gap-y-4">
            @foreach($categories as $cat)
                <a href="{{ route('storefront.search', ['category_ids' => [$cat->id]]) }}" class="flex flex-col items-center gap-1.5">
                    <div class="w-14 h-14 rounded-2xl bg-white border border-gray-100 flex items-center justify-center font-bold text-sm text-emerald-800">{{ strtoupper(mb_substr($cat->name, 0, 2)) }}</div>
                    <span class="text-[11px] text-gray-700 text-center leading-tight">{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>

    <a href="{{ route('storefront.stores') }}" class="mt-6 bg-white rounded-xl border border-gray-100 p-3.5 flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-[13px] font-semibold text-gray-900">Jelajahi Semua Toko</p>
            <p class="text-[11px] text-gray-500 mt-0.5">Lihat toko terdekat dari lokasimu</p>
        </div>
        <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    </a>
</main>
@endsection

@extends('customer.layouts.app')
@section('title', 'Favorit Saya')

@section('content')
<header class="sticky top-0 z-30 bg-white border-b border-gray-100 px-4 py-3">
    <h1 class="font-bold text-[15px] text-gray-900">Favorit Saya</h1>
</header>

<main class="px-4">
    @if($products->isEmpty())
        <div class="flex flex-col items-center justify-center text-center px-8 py-24">
            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                <svg class="w-7 h-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            </div>
            <p class="text-sm font-semibold text-gray-800">Belum ada produk favorit</p>
            <p class="text-xs text-gray-500 mt-1.5">Produk yang kamu simpan akan muncul di sini</p>
            <a href="{{ route('storefront.index') }}" class="mt-4 text-xs font-semibold text-white bg-emerald-700 rounded-lg px-5 py-2.5">Jelajahi Produk</a>
        </div>
    @else
        <div class="grid grid-cols-2 gap-3 mt-4">
            @foreach($products as $product)
                @include('customer.storefront.partials.product-card', ['product' => $product, 'showStore' => true])
            @endforeach
        </div>
    @endif
</main>
@endsection
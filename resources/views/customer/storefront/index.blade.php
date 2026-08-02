@extends('customer.layouts.app')
@section('title', 'Daftar Toko')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Toko Terdekat</h1>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pilih toko untuk mulai berbelanja.</p>
</div>

<form method="GET" action="{{ route('storefront.index') }}" class="mb-6 flex max-w-md gap-2">
    <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari toko..."
           class="w-full rounded-lg border-gray-300 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
    <button type="submit"
            class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">Cari</button>
</form>

@if($stores->isEmpty())
    <div class="rounded-2xl border border-dashed border-gray-300 p-12 text-center text-gray-500 dark:border-gray-700 dark:text-gray-400">
        Belum ada toko aktif.
    </div>
@else
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($stores as $store)
            <a href="{{ route('storefront.store', $store->slug) }}"
               class="group rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:border-indigo-300 hover:shadow-md dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-700">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">
                        @svg('heroicon-o-building-storefront', 'h-6 w-6')
                    </div>
                    @if($store->locationNode)
                        <span class="inline-flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                            @svg('heroicon-o-map-pin', 'h-3.5 w-3.5')
                            {{ $store->locationNode->name }}
                        </span>
                    @endif
                </div>

                <h2 class="mt-4 text-lg font-semibold text-gray-900 group-hover:text-indigo-600 dark:text-white dark:group-hover:text-indigo-400">
                    {{ $store->store_name }}
                </h2>

                @if($store->description)
                    <p class="mt-1 line-clamp-2 text-sm text-gray-500 dark:text-gray-400">{{ $store->description }}</p>
                @endif

                <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-4 text-sm dark:border-gray-800">
                    <span class="text-gray-500 dark:text-gray-400">{{ $store->products_count }} produk</span>
                    <span class="font-medium text-indigo-600 dark:text-indigo-400">Kunjungi →</span>
                </div>
            </a>
        @endforeach
    </div>

    <div class="mt-8">
        {{ $stores->links() }}
    </div>
@endif
@endsection

@extends('customer.layouts.app')
@section('title', $product->name)

@section('content')
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
                @if($product->category)
                    <span class="mt-2 inline-block rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">
                        {{ $product->category->name }}
                    </span>
                @endif
                @if($product->description)
                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">{{ $product->description }}</p>
                @endif
            </div>
        </div>
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
                            Rp {{ number_format((float) $variant->price, 0, ',', '.') }}
                        </p>

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
@endsection

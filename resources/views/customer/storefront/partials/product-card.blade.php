@php
    $showStore = $showStore ?? true;
    $route = $route ?? null;
    $href = $route ?? route('storefront.product', [$product->store->slug, $product->slug]);
    $isFavorite = auth('customer')->check()
        && auth('customer')->user()->favoriteProducts()->whereKey($product->id)->exists();
    $store = $product->store;
    $premium = $store && str_contains(strtolower($store->level?->name ?? ''), 'premium');
@endphp

<div class="relative bg-white rounded-xl border border-gray-100 overflow-hidden flex flex-col">
    <div class="relative aspect-square bg-gray-100 overflow-hidden shrink-0">
        <a href="{{ $href }}" class="absolute inset-0">
            @if($product->primary_image)
                <img src="{{ asset('storage/' . $product->primary_image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
            @endif
            @if($product->discount_percent > 0)
                <span class="absolute top-1.5 left-1.5 bg-red-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded">-{{ $product->discount_percent }}%</span>
            @endif
        </a>
        @auth('customer')
            <form method="POST" action="{{ route('customer.favorite.toggle') }}" class="absolute top-1.5 right-1.5 z-10"
                  data-favform="{{ $product->id }}"
                  x-data="{
                    isFav: {{ $isFavorite ? 'true' : 'false' }},
                    toggle() {
                        if (this.isFav) {
                            $customerConfirm({
                                title: 'Hapus dari Favorit?',
                                message: 'Produk akan dihapus dari daftar favoritmu.',
                                confirmText: 'Ya, Hapus',
                                variant: 'danger'
                            }).then(ok => ok && this.$refs.favForm.submit());
                        } else {
                            this.$refs.favForm.submit();
                        }
                    }
                }" x-ref="favForm">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <button type="button" @click="toggle()" title="{{ $isFavorite ? 'Hapus dari favorit' : 'Tambah ke favorit' }}"
                        class="w-8 h-8 rounded-full bg-white/90 backdrop-blur shadow-sm flex items-center justify-center">
                    <svg class="w-4 h-4 {{ $isFavorite ? 'text-red-500 fill-red-500' : 'text-gray-700 fill-white/30' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </button>
            </form>
        @else
            <span class="absolute top-1.5 right-1.5 z-10 w-8 h-8 rounded-full bg-white/90 backdrop-blur shadow-sm flex items-center justify-center">
                <svg class="w-4 h-4 text-gray-700 fill-white/30" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            </span>
        @endauth
    </div>

    <div class="p-2.5 flex-1 flex flex-col">
        <a href="{{ $href }}">
            <p class="text-[13px] leading-tight line-clamp-2 text-gray-900">{{ $product->name }}</p>
        </a>
        @if($showStore && $store)
            <p class="text-[11px] text-gray-500 flex items-center gap-1 mt-1 truncate">
                @if($premium)
                    <span class="text-amber-500 shrink-0">👑</span>
                @endif
                <span class="truncate">{{ $store->store_name }}</span>
            </p>
        @endif
        @if($product->min_price !== null)
            <div class="flex flex-wrap items-baseline gap-x-1.5 gap-y-0.5 mt-1">
                @if($product->discount_percent > 0 && $product->min_original_price > $product->min_price)
                    <span class="text-[11px] text-gray-400 line-through whitespace-nowrap">Rp{{ number_format((float) $product->min_original_price, 0, ',', '.') }}</span>
                @endif
                &nbsp;<span class="text-sm font-bold text-emerald-700 whitespace-nowrap">Rp{{ number_format((float) $product->min_price, 0, ',', '.') }}</span>
            </div>
        @endif
        <p class="text-[11px] text-gray-500 mt-0.5">
            @if($product->avg_rating)
                ⭐{{ number_format($product->avg_rating, 1) }} ({{ $product->rating_count }})
                @if($product->sold_count > 0) · Terjual {{ $product->sold_count }} @endif
            @else
                @if($product->sold_count > 0) Terjual {{ $product->sold_count }} @endif
            @endif
        </p>
        @if($showStore && $store?->locationNode)
            <div class="mt-1.5 inline-flex items-center gap-1 text-[10px] font-medium bg-emerald-50 text-emerald-800 rounded-full px-2 py-0.5">
                <svg class="w-2.5 h-2.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                {{ $store->locationNode->name }} (Rp{{ number_format((float) $store->rate_per_km * 1000, 0, ',', '.') }}/km)
            </div>
        @endif
    </div>
</div>
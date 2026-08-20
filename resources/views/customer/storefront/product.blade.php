@extends('customer.layouts.app')
@section('title', $product->name)

@section('content')
@php
    $pricingService = app(\App\Services\Pricing\PromotionPricingService::class);
    $variantData = $product->variants->map(fn ($v) => [
        'id' => $v->id,
        'attrs' => $v->attributeValues->map(fn ($av) => [
            'name' => $av->attribute?->name ?? 'Varian',
            'value_id' => $av->id,
            'label' => $av->value,
        ])->values(),
        'price' => (float) $pricingService->pricing($v)['effective'],
        'original' => (float) $pricingService->pricing($v)['original'],
        'stock' => (int) $v->stock,
        'image' => $v->images->first()?->path ? asset('storage/' . $v->images->first()->path) : null,
    ])->values();

    $groups = collect();
    foreach ($variantData as $v) {
        foreach ($v['attrs'] as $a) {
            $groups->put($a['name'], $groups->get($a['name'], collect())->put($a['value_id'], $a['label']));
        }
    }
    $defaultSelection = $variantData->first()['attrs'] ?? [];
    $defaultSelection = collect($defaultSelection)->mapWithKeys(fn ($a) => [$a['name'] => $a['value_id']])->all();

    $initialVariant = $product->variants->first();
    $initialPricing = $initialVariant ? $pricingService->pricing($initialVariant) : null;

    $isFavorite = auth('customer')->check()
        && auth('customer')->user()->favoriteProducts()->whereKey($product->id)->exists();

    $ratingBars = [];
    foreach ([5, 4, 3, 2, 1] as $star) {
        $count = $product->ratings->where('rating', $star)->count();
        $ratingBars[$star] = $product->ratings->isNotEmpty() ? round($count / $product->ratings->count() * 100) : 0;
    }
    $premiumStore = str_contains(strtolower($store->level?->name ?? ''), 'premium');
@endphp

<div class="pb-28" x-data="productPage($el)">

    <script type="application/json" data-role="variant-data">@json($variantData->all())</script>
    <script type="application/json" data-role="group-data">@json($groups->map->all()->all())</script>
    <script type="application/json" data-role="selection-data">@json($defaultSelection)</script>
    <script type="application/json" data-role="gallery-data">@json($product->images->pluck('path')->map(fn ($p) => asset('storage/' . $p))->values()->all())</script>

    <header class="absolute top-0 inset-x-0 z-30 px-4 pt-3 flex items-center justify-between">
        <a href="{{ url()->previous() && ! request()->routeIs('storefront.product') ? url()->previous() : route('storefront.store', $store->slug) }}"
           class="w-9 h-9 flex items-center justify-center rounded-full bg-white/90 backdrop-blur shadow-sm">
            <svg class="w-5 h-5 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <button class="w-9 h-9 flex items-center justify-center rounded-full bg-white/90 backdrop-blur shadow-sm" onclick="navigator.share? navigator.share({title: document.title, url: location.href}) : null">
            <svg class="w-[18px] h-[18px] text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342a3 3 0 100-2.684m0 2.684a3 3 0 100 2.684m0-2.684l6.632 3.316m0-6l-6.632 3.316m6.632-3.316a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 6a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"/></svg>
        </button>
    </header>

    <section class="relative">
        <div class="flex overflow-x-auto no-scrollbar snap-x-mandatory min-h-[320px]" x-ref="gallery" @scroll="idx = Math.round($el.scrollLeft / $el.clientWidth)">
            <template x-for="(img, i) in galleryImages" :key="i">
                <div class="w-full aspect-square bg-gray-100 shrink-0 snap-center overflow-hidden">
                    <img :src="img" alt="Gambar produk" class="w-full h-full object-cover">
                </div>
            </template>
        </div>
        <span class="absolute bottom-3 right-3 bg-black/50 text-white text-[11px] font-medium rounded-full px-2.5 py-1"
              x-show="galleryCount > 1" x-text="(idx + 1) + '/' + galleryCount">1/{{ $product->images->count() }}</span>
    </section>

    <main class="px-4">

        <section class="mt-4">
            <h1 class="text-[17px] font-bold text-gray-900 leading-snug">{{ $product->name }}</h1>
            <div class="flex items-baseline gap-2 mt-1.5">
                @if($initialPricing && (float) $initialPricing['discount'] > 0)
                    <span class="text-sm text-gray-400 line-through" style="display:none"
                          x-show="selected && selected.original > selected.price" x-text="fmt(selected.original)">
                        Rp{{ number_format((float) $initialPricing['original'], 0, ',', '.') }}
                    </span>
                @endif
                <span class="text-xl font-extrabold text-emerald-700" x-text="selected ? fmt(selected.price) : '—'">
                    @if($initialPricing)
                        Rp{{ number_format((float) $initialPricing['effective'], 0, ',', '.') }}
                    @endif
                </span>
            </div>
            <a href="#reviews" class="inline-flex items-center gap-1 text-xs text-gray-600 mt-1.5">
                @if($product->avg_rating)
                    <span class="text-amber-500">★</span>
                    <span class="font-semibold text-gray-800">{{ number_format($product->avg_rating, 1) }}</span>
                    <span>({{ $product->rating_count }} ulasan)</span>
                    @if($product->sold_count > 0) · Terjual {{ $product->sold_count }} @endif
                @else
                    <span class="text-gray-400">Belum ada ulasan</span>
                @endif
            </a>
        </section>

        @if($product->variants->isEmpty())
            <section class="mt-6 rounded-xl bg-gray-50 border border-gray-200 p-4 text-center text-sm text-gray-500">
                Produk belum memiliki varian aktif.
            </section>
        @else
            <section class="mt-5">
                @foreach($groups as $attrName => $values)
                    <p class="text-[13px] font-semibold text-gray-800 mb-2">{{ $attrName }}</p>
                    <div class="flex flex-wrap gap-2 mb-4">
                        @foreach($values as $valueId => $label)
                            <button type="button"
                                    @click="select('{{ $attrName }}', '{{ $valueId }}')"
                                    :disabled="!usable('{{ $attrName }}', '{{ $valueId }}')"
                                    x-bind:class="selection['{{ $attrName }}'] === '{{ $valueId }}'
                                        ? 'bg-emerald-700 text-white'
                                        : (usable('{{ $attrName }}', '{{ $valueId }}')
                                            ? 'bg-white border border-gray-200 text-gray-700'
                                            : 'bg-gray-50 border border-gray-200 text-gray-300 line-through cursor-not-allowed')"
                                    class="text-xs font-medium rounded-full px-3.5 py-2">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                @endforeach
            </section>

            <section class="mt-2 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <template x-if="selected">
                        <span x-bind:class="selected.stock === 0 ? 'text-red-600 bg-red-50' : (selected.stock <= 5 ? 'text-amber-600 bg-amber-50' : '')"
                              class="text-xs font-medium rounded-full px-2.5 py-1"
                              x-text="selected.stock === 0 ? 'Stok habis' : 'Stok tersisa ' + selected.stock"></span>
                    </template>
                </div>
                <div class="flex items-center border border-gray-200 rounded-full">
                    <button type="button" @click="dec" class="w-8 h-8 flex items-center justify-center text-gray-500 text-lg font-medium">−</button>
                    <span class="w-8 text-center text-sm font-semibold text-gray-900" x-text="qty">{{ $initialVariant ? 1 : '' }}</span>
                    <button type="button" @click="inc" class="w-8 h-8 flex items-center justify-center text-gray-700 text-lg font-medium">+</button>
                </div>
            </section>

            <div class="mt-3 text-xs font-medium text-red-600 bg-red-50 rounded-full px-2.5 py-1 w-fit" x-show="selected === null">
                Kombinasi varian tidak tersedia
            </div>
        @endif

        <section id="reviews" class="mt-6 bg-white rounded-xl border border-gray-100 p-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-bold text-[15px] text-gray-900">Ulasan Produk</h2>
                @if($product->ratings->count() > 2)
                    <button type="button" @click="reviewsOpen = !reviewsOpen" class="text-xs font-semibold text-emerald-700"
                            x-text="reviewsOpen ? 'Tutup' : 'Lihat Semua'">Lihat Semua</button>
                @endif
            </div>
            @if($product->ratings->isEmpty())
                <p class="text-xs text-gray-500 py-2">Belum ada ulasan untuk produk ini.</p>
            @else
                <div class="flex items-center gap-4">
                    <div class="text-center shrink-0">
                        <p class="text-3xl font-extrabold text-gray-900">{{ number_format($product->avg_rating, 1) }}</p>
                        <p class="text-amber-500 text-xs mt-0.5">★★★★★</p>
                        <p class="text-[11px] text-gray-500 mt-0.5">{{ $product->ratings->count() }} ulasan</p>
                    </div>
                    <div class="flex-1 space-y-1">
                        @foreach($ratingBars as $star => $pct)
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] text-gray-500 w-2">{{ $star }}</span>
                                <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-amber-400 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100 space-y-3">
                    @foreach($product->ratings as $rating)
                        <div class="flex gap-2.5"
                             x-show="reviewsOpen || {{ $loop->index }} < 2">
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
                                <p class="text-[10px] text-gray-400 mt-1">{{ $rating->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="mt-5 bg-white rounded-xl border border-gray-100 p-3.5">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-sm shrink-0">
                    {{ strtoupper(substr($store->store_name, 0, 2)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-gray-900 truncate flex items-center gap-1">
                        {{ $store->store_name }}
                        @if($premiumStore)<span class="text-amber-500 text-xs">👑</span>@endif
                    </p>
                    <p class="text-[11px] text-gray-500 mt-0.5">
                        @if($store->avg_rating)
                            ★ {{ number_format($store->avg_rating, 1) }} ({{ $store->rating_count }} ulasan) ·
                        @endif
                        Bergabung sejak {{ $store->created_at->format('M Y') }}
                    </p>
                    @if($store->locationNode)
                        <div class="mt-1.5 inline-flex items-center gap-1 text-[11px] font-medium bg-emerald-50 text-emerald-800 rounded-full px-2 py-1">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ $store->locationNode->name }}
                            @if($shipping['distance_km'] !== null)
                                ({{ number_format($shipping['distance_km'], 1, ',', '.') }} km)
                            @else
                                (Rp{{ number_format((float) $store->rate_per_km * 1000, 0, ',', '.') }}/km)
                            @endif
                        </div>
                    @endif
                </div>
            </div>
            <a href="{{ route('storefront.store', $store->slug) }}"
               class="block w-full mt-3 text-xs font-semibold text-emerald-700 border border-emerald-700 rounded-lg py-2 text-center">Kunjungi Toko</a>
        </section>

        <section class="mt-5">
            <h2 class="font-bold text-[15px] text-gray-900 mb-2">Deskripsi Produk</h2>
            <p class="text-[13px] text-gray-600 leading-relaxed" x-bind:class="descOpen ? '' : 'line-clamp-4'">
                {{ $product->description ?: 'Belum ada deskripsi untuk produk ini.' }}
            </p>
            @if(strlen($product->description ?? '') > 160)
                <button type="button" @click="descOpen = !descOpen" class="text-xs font-semibold text-emerald-700 mt-1.5"
                        x-text="descOpen ? 'Tutup' : 'Lihat selengkapnya'"></button>
            @endif
        </section>

        @if($other_products->isNotEmpty())
            <section class="mt-6">
                <h2 class="font-bold text-[15px] text-gray-900 mb-2.5">Produk Lain dari Toko Ini</h2>
                <div class="flex gap-3 overflow-x-auto no-scrollbar pb-1">
                    @foreach($other_products as $p)
                        <div class="shrink-0 w-36">
                            @include('customer.storefront.partials.product-card', ['product' => $p, 'showStore' => false])
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

    </main>

    @unless($product->variants->isEmpty())
    <div class="fixed bottom-0 inset-x-0 max-w-[420px] mx-auto bg-white border-t border-gray-100 px-3 py-2.5 flex items-center gap-2 z-40">
        @auth('customer')
            <form method="POST" action="{{ route('customer.favorite.toggle') }}">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <button type="submit" class="w-11 h-11 shrink-0 flex items-center justify-center rounded-full border {{ $isFavorite ? 'border-red-300 text-red-500' : 'border-gray-200 text-gray-500' }}">
                    <svg class="w-5 h-5 {{ $isFavorite ? 'fill-red-500' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </button>
            </form>
            <form method="POST" action="{{ route('customer.chat.start') }}">
                @csrf
                <input type="hidden" name="store_id" value="{{ $store->id }}">
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <button type="submit" class="w-11 h-11 shrink-0 flex items-center justify-center rounded-full border border-emerald-700 text-emerald-700">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </button>
            </form>
            <form method="POST" action="{{ route('customer.cart.store') }}" class="flex-1 flex gap-2" @submit="if (!selected || selected.stock === 0) { $event.preventDefault(); }">
                @csrf
                <input type="hidden" name="variant_id" :value="selected ? selected.id : ''">
                <input type="hidden" name="qty" :value="qty">
                <button type="submit" x-bind:disabled="!selected || selected.stock === 0"
                        class="flex-1 text-xs font-semibold text-emerald-700 border border-emerald-700 rounded-lg py-3 disabled:opacity-40">Tambah ke Keranjang</button>
            </form>
            <form method="POST" action="{{ route('customer.cart.store') }}" class="flex-1" @submit="if (!selected || selected.stock === 0) { $event.preventDefault(); }">
                @csrf
                <input type="hidden" name="variant_id" :value="selected ? selected.id : ''">
                <input type="hidden" name="qty" :value="qty">
                <input type="hidden" name="checkout" value="1">
                <button type="submit" x-bind:disabled="!selected || selected.stock === 0"
                        class="flex-1 text-xs font-semibold text-white bg-emerald-700 rounded-lg py-3 disabled:opacity-40">Beli Sekarang</button>
            </form>
        @else
            <a href="{{ route('customer.auth.login') }}" class="flex-1 block text-center text-xs font-semibold text-white bg-emerald-700 rounded-lg py-3">Masuk untuk membeli</a>
        @endauth
    </div>
    @endunless

</div>

@push('scripts')
<style>
  .snap-x-mandatory{scroll-snap-type:x mandatory}
  .snap-center{scroll-snap-align:center}
</style>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('productPage', (el) => {
        const get = (role) => JSON.parse(el.querySelector('[data-role="' + role + '"]').textContent);
        return {
            variants: get('variant-data'),
            groups: get('group-data'),
            selection: get('selection-data'),
            gallery: get('gallery-data'),
            qty: 1,
            descOpen: false,
            reviewsOpen: false,
            idx: 0,
            fmt(n){ return 'Rp ' + Math.round(n).toLocaleString('id-ID'); },
            get selected() {
                return this.variants.find(v => v.attrs.every(a => this.selection[a.name] === a.value_id)) || null;
            },
            select(name, valueId) {
                this.selection[name] = valueId;
                this.idx = 0;
            },
            usable(name, valueId) {
                const trial = Object.assign({}, this.selection, { [name]: valueId });
                return this.variants.some(v => v.attrs.every(a => trial[a.name] === a.value_id));
            },
            get galleryImages() {
                const s = this.selected;
                if (s && s.image) return [s.image];
                return this.gallery;
            },
            get galleryCount() { return this.galleryImages.length; },
            dec(){ if (this.qty > 1) this.qty--; },
            inc(){ const s = this.selected; if (s && this.qty < s.stock) this.qty++; },
        };
    });
});
</script>
@endpush
@endsection
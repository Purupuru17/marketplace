@extends('customer.layouts.app')
@section('title', $order->hasBeenReviewed() ? 'Ulasan Kamu' : 'Beri Ulasan')

@php
    $unrated = $order->items->filter(fn ($item) => ! $item->rating);
    $rated = $order->items->filter(fn ($item) => (bool) $item->rating);
    $backHref = route('customer.order.show', $order);
@endphp

@section('content')
<div class="pb-24" x-data="{
    ratings: @js($unrated->mapWithKeys(fn ($item) => [$item->id => ['rating' => 5, 'review' => '']])->all()),
    get storeScore() {
        const vals = Object.values(this.ratings).map(r => Number(r.rating));
        return vals.length ? Math.round(vals.reduce((a, b) => a + b, 0) / vals.length) : 0;
    },
}">

    <header class="sticky top-0 z-30 bg-white border-b border-gray-100 px-4 py-3 flex items-center gap-3">
        <a href="{{ $backHref }}" class="w-8 h-8 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="font-bold text-[15px] text-gray-900">{{ $unrated->isNotEmpty() ? 'Beri Ulasan' : 'Ulasan Kamu' }}</h1>
    </header>

    <main class="px-4">
        <p class="text-[11px] text-gray-500 mt-3">{{ $order->order_no }} · {{ $order->store->store_name }}</p>

        @foreach($rated as $item)
            @php $img = $item->product?->images->first()?->path ? asset('storage/' . $item->product->images->first()->path) : null; @endphp
            <section class="mt-3 bg-white rounded-xl border border-gray-100 p-3.5 opacity-90">
                <div class="flex gap-3">
                    @if($img)
                        <div class="w-14 h-14 rounded-lg bg-gray-100 shrink-0 overflow-hidden">
                            <img src="{{ $img }}" alt="" class="w-full h-full object-cover">
                        </div>
                    @else
                        <div class="w-14 h-14 rounded-lg bg-gray-100 shrink-0"></div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <p class="text-[13px] font-medium text-gray-900 leading-snug">{{ $item->name_snapshot }}</p>
                        <p class="text-amber-500 text-xs mt-1">
                            @for($i = 1; $i <= 5; $i++){{ $i <= $item->rating->rating ? '★' : '☆' }}@endfor
                        </p>
                    </div>
                </div>
                @if($item->rating->review)
                    <p class="text-[13px] text-gray-600 mt-3 leading-relaxed">{{ $item->rating->review }}</p>
                @endif
                <div class="mt-2 flex items-center justify-between">
                    <p class="text-[10px] text-gray-400">Dikirim {{ $item->rating->created_at->format('d M Y') }} · Tidak bisa diubah</p>
                    <form method="POST" action="{{ route('customer.rating.destroy', $item->rating->id) }}" x-ref="delReview{{ $item->id }}">
                        @csrf @method('DELETE')
                        <button type="button" @click="$customerConfirm({ title: 'Hapus Ulasan?', message: 'Ulasan akan dihapus dan kamu bisa menilai ulang produk ini.', confirmText: 'Ya, Hapus', variant: 'danger' }).then(ok => ok && $refs['delReview{{ $item->id }}'].submit())"
                                class="text-[10px] font-medium text-red-500 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Hapus
                            </button>
                    </form>
                </div>
            </section>
        @endforeach

        @if($unrated->isNotEmpty())
            <form id="reviewSubmit" method="POST" action="{{ route('customer.order.review.store', $order) }}">
                @csrf

                @foreach($unrated as $item)
                    @php $img = $item->product?->images->first()?->path ? asset('storage/' . $item->product->images->first()->path) : null; @endphp
                    <section class="mt-3 bg-white rounded-xl border border-gray-100 p-3.5">
                        <div class="flex gap-3">
                            @if($img)
                                <div class="w-14 h-14 rounded-lg bg-gray-100 shrink-0 overflow-hidden">
                                    <img src="{{ $img }}" alt="" class="w-full h-full object-cover">
                                </div>
                            @else
                                <div class="w-14 h-14 rounded-lg bg-gray-100 shrink-0"></div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="text-[13px] font-medium text-gray-900 leading-snug">{{ $item->name_snapshot }}</p>
                                <p class="text-[11px] text-gray-500 mt-0.5">{{ $item->variant_snapshot ? 'Kemasan: ' . $item->variant_snapshot : 'Tanpa varian' }}</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-center gap-2 mt-4">
                            @for($i = 1; $i <= 5; $i++)
                                <label class="cursor-pointer">
                                    <input type="radio" name="ratings[{{ $item->id }}][rating]" value="{{ $i }}" class="sr-only"
                                           x-model.number="ratings['{{ $item->id }}'].rating">
                                    <svg :class="ratings['{{ $item->id }}'].rating >= {{ $i }} ? 'text-amber-400' : 'text-gray-200'"
                                         class="w-8 h-8 transition" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                </label>
                            @endfor
                        </div>
                        <p class="text-center text-[11px] mt-1 font-semibold text-amber-600"
                           x-text="['', 'Buruk', 'Kurang', 'Cukup', 'Baik', 'Sangat Baik'][ratings['{{ $item->id }}'].rating]"></p>

                        <textarea x-model="ratings['{{ $item->id }}'].review"
                                  name="ratings[{{ $item->id }}][review]" rows="3"
                                  placeholder="Ceritakan pengalamanmu dengan produk ini... (opsional)"
                                  class="w-full mt-3 text-[13px] text-gray-700 border border-gray-200 rounded-lg p-3 resize-none placeholder:text-gray-400 focus:ring-0"></textarea>
                        @error('order_item_id')
                            <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                        @enderror
                    </section>
                @endforeach

                <section class="mt-3 bg-white rounded-xl border border-gray-100 p-3.5">
                    <p class="text-[13px] font-semibold text-gray-800">Nilai Toko {{ $order->store->store_name }}</p>
                    <div class="flex items-center justify-center gap-2 mt-3">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="w-8 h-8" :class="storeScore >= {{ $i }} ? 'text-amber-400' : 'text-gray-200'"
                                 fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        @endfor
                    </div>
                    <p class="text-center text-[11px] text-gray-400 mt-1">Nilai toko dihitung dari rata-rata penilaian produkmu</p>
                </section>
            </form>
        @endif
    </main>

    @if($unrated->isNotEmpty())
        <div class="fixed bottom-14 inset-x-0 max-w-[420px] mx-auto bg-white border-t border-gray-100 px-4 py-3 z-30">
<button type="submit" form="reviewSubmit"
                        class="w-full text-sm font-semibold text-white bg-emerald-700 rounded-lg py-3 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                        Kirim Ulasan
                    </button>
        </div>
    @endif
</div>
@endsection
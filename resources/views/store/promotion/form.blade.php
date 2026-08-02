@extends('idcore::layouts.backend')
@section('title', $promotion ? 'Edit Promo' : 'Tambah Promo')

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $promotion ? 'Edit Promo' : 'Tambah Promo' }}</h1>
        <x-idcore::breadcrumb :items="[['label' => 'Home', 'url' => route('dashboard')], ['label' => 'Promo'], ['label' => 'Promo', 'url' => route('toko.promotion.index')], ['label' => $promotion ? 'Edit' : 'Create']]" />
    </div>
</div>

@php
    $promoSource = old('source', $promotion->source ?? ($isAdmin ? 'platform' : 'store'));
    $promoStoreId = old('store_id', $promotion->store_id ?? ($isAdmin ? null : array_key_first($storeOptions)));
    $promoValue = old('value', $promotion->value ?? null);
    $startValue = old('starts_at', $promotion?->starts_at?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i'));
    $endValue = old('ends_at', $promotion?->ends_at?->format('Y-m-d\TH:i') ?? now()->addMonth()->format('Y-m-d\TH:i'));
@endphp

<x-idcore::card title="{{ $promotion ? 'Edit Promo' : 'Tambah Promo' }}" subtitle="Diskon otomatis untuk produk terpilih." class="max-w-4xl">
    <form action="{{ $promotion ? route('toko.promotion.update', $promotion->id) : route('toko.promotion.store') }}" method="POST" class="space-y-6"
          x-data="{ source: '{{ $promoSource }}' }">
        @csrf
        @if($promotion) @method('PUT') @endif

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            @if($isAdmin)
                <x-idcore::select name="source" label="Sumber Promo" :options="['platform' => 'Platform', 'store' => 'Toko']" :selected="$promoSource" required x-model="source" />
                <div x-show="source === 'store'">
                    <x-idcore::select name="store_id" label="Toko" :options="$storeOptions" :selected="$promoStoreId" placeholder="Pilih toko" required />
                </div>
            @else
                <input type="hidden" name="source" value="store">
                <input type="hidden" name="store_id" value="{{ $promoStoreId }}">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sumber Promo</label>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Promo Toko — {{ array_key_exists($promoStoreId, $storeOptions) ? $storeOptions[$promoStoreId] : '-' }}</p>
                </div>
            @endif

            <x-idcore::input name="name" label="Nama Promo" required :value="old('name', $promotion->name ?? null)" placeholder="Contoh: Promo Akhir Pekan" />
            <x-idcore::select name="type" label="Tipe Diskon" :options="['percentage' => 'Persentase (%)', 'fixed' => 'Nominal (Rp)']" :selected="old('type', $promotion->type ?? 'percentage')" required />
            <x-idcore::input name="value" label="Nilai Diskon" type="number" step="0.01" min="0.01" required :value="$promoValue" placeholder="Contoh: 10 (untuk 10%)" />
            <x-idcore::input name="starts_at" label="Mulai" type="datetime-local" required :value="$startValue" />
            <x-idcore::input name="ends_at" label="Berakhir" type="datetime-local" required :value="$endValue" />
        </div>

        <div class="flex items-center gap-2">
            <input type="hidden" name="stackable" value="0">
            <input type="checkbox" name="stackable" value="1" id="stackable"
                   @checked(old('stackable', $promotion->stackable ?? false))
                   class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900">
            <label for="stackable" class="text-sm text-gray-600 dark:text-gray-400">Bisa digabung dengan promo lain</label>
        </div>

        <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Produk</h3>
            <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Pilih produk yang mendapat diskon ini. Kosongkan untuk semua produk.</p>
            <div class="grid max-h-72 grid-cols-1 gap-2 overflow-y-auto rounded-lg border border-gray-200 p-4 dark:border-gray-700 sm:grid-cols-2">
                @forelse($productOptions as $id => $name)
                    <label class="flex cursor-pointer items-start gap-2 rounded-md px-2 py-1.5 hover:bg-gray-50 dark:hover:bg-gray-800/60">
                        <input type="checkbox" name="products[]" value="{{ $id }}"
                               @checked(in_array($id, old('products', $selectedProducts->toArray()), true))
                               class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $name }}</span>
                    </label>
                @empty
                    <p class="col-span-full text-sm text-gray-500 dark:text-gray-400">Belum ada produk aktif.</p>
                @endforelse
            </div>
        </div>

        <x-idcore::select name="status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive']" :selected="old('status', $promotion->status ?? 'active')" required />

        <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 dark:border-gray-800 sm:flex-row sm:justify-end">
            <x-idcore::button variant="outline" @click.prevent="window.location.href = '{{ route('toko.promotion.index') }}'">Batal</x-idcore::button>
            <x-idcore::button type="submit" variant="{{ $promotion ? 'warning' : 'success' }}">Simpan</x-idcore::button>
        </div>
    </form>
</x-idcore::card>
@endsection

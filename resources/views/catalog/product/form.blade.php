@extends('idcore::layouts.backend')
@section('title', $product ? 'Edit Produk' : 'Tambah Produk')

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $product ? 'Edit Produk' : 'Tambah Produk' }}</h1>
        <x-idcore::breadcrumb :items="[['label' => 'Home', 'url' => route('dashboard')], ['label' => 'Katalog'], ['label' => 'Produk', 'url' => route('katalog.product.index')], ['label' => $product ? 'Edit' : 'Create']]" />
    </div>
</div>

<x-idcore::card title="{{ $product ? 'Edit Produk' : 'Tambah Produk' }}" subtitle="Data produk milik toko tertentu." class="max-w-4xl">
    <form action="{{ $product ? route('katalog.product.update', $product->id) : route('katalog.product.store') }}" method="POST" class="space-y-6"
          x-data="{ formDirty: false }"
          @input.debounce.500ms="formDirty = true"
          @change="formDirty = true"
          @submit="formDirty = false"
          x-init="$watch('formDirty', val => { window.onbeforeunload = val ? () => true : null; })">
        @csrf
        @if($product) @method('PUT') @endif

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-idcore::select name="store_id" label="Toko" :options="$storeOptions" :selected="$product->store_id ?? null" placeholder="Pilih toko" required />
            <x-idcore::select name="category_id" label="Kategori" :options="$categoryOptions" :selected="$product->category_id ?? null" placeholder="Tanpa kategori" />
            <x-idcore::input name="name" label="Nama Produk" required :value="$product->name ?? null" placeholder="Contoh: Nasi Goreng Spesial" />
        </div>

        <x-idcore::textarea name="description" label="Deskripsi" :value="$product->description ?? null" placeholder="Deskripsi produk" />

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-idcore::select name="status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive']" :selected="$product->status ?? 'active'" required />
            <div class="flex items-end pb-1">
                <x-idcore::toggle name="is_featured" label="Produk unggulan" :checked="$product->is_featured ?? false" />
            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 dark:border-gray-800 sm:flex-row sm:justify-end">
            <x-idcore::button variant="outline" @click.prevent="
                if (formDirty) {
                    $confirm({
                        title: 'Perubahan Belum Disimpan',
                        message: 'Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman ini?',
                        confirmText: 'Ya, Tinggalkan',
                        cancelText: 'Batal',
                        variant: 'warning'
                    }).then(ok => { if (ok) window.location.href = '{{ route('katalog.product.index') }}'; });
                } else {
                    window.location.href = '{{ route('katalog.product.index') }}';
                }
            ">Batal</x-idcore::button>
            <x-idcore::button type="submit" variant="{{ $product ? 'warning' : 'success' }}">Simpan</x-idcore::button>
        </div>
    </form>
</x-idcore::card>
@endsection

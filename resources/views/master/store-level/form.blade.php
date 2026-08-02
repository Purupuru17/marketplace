@extends('idcore::layouts.backend')
@section('title', $storeLevel ? 'Edit Store Level' : 'Tambah Store Level')

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $storeLevel ? 'Edit Store Level' : 'Tambah Store Level' }}</h1>
        <x-idcore::breadcrumb :items="[['label' => 'Home', 'url' => route('dashboard')], ['label' => 'Master Data'], ['label' => 'Store Level', 'url' => route('master.store-level.index')], ['label' => $storeLevel ? 'Edit' : 'Create']]" />
    </div>
</div>

<x-idcore::card title="{{ $storeLevel ? 'Edit Store Level' : 'Tambah Store Level' }}" subtitle="Atur paket level berlangganan store." class="max-w-2xl">
    <form action="{{ $storeLevel ? route('master.store-level.update', $storeLevel->id) : route('master.store-level.store') }}" method="POST" class="space-y-6"
          x-data="{ formDirty: false }"
          @input.debounce.500ms="formDirty = true"
          @change="formDirty = true"
          @submit="formDirty = false"
          x-init="$watch('formDirty', val => { window.onbeforeunload = val ? () => true : null; })">
        @csrf
        @if($storeLevel) @method('PUT') @endif

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-idcore::input name="name" label="Nama" required :value="$storeLevel->name ?? null" placeholder="Contoh: Basic" />
            <x-idcore::input name="price" label="Harga (Rp)" type="number" step="0.01" min="0" required :value="$storeLevel->price ?? null" placeholder="0" />
            <x-idcore::input name="max_products" label="Maks Produk" type="number" min="0" :value="$storeLevel->max_products ?? null" placeholder="Kosongkan jika tidak terbatas" />
            <x-idcore::input name="max_discount" label="Maks Diskon (%)" type="number" step="0.01" min="0" max="100" :value="$storeLevel->max_discount ?? null" placeholder="0" />
            <x-idcore::input name="sort_order" label="Urutan" type="number" min="0" :value="$storeLevel->sort_order ?? null" placeholder="0" />
            <x-idcore::select name="status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive']" :selected="$storeLevel->status ?? 'active'" required />
        </div>

        <x-idcore::toggle name="can_run_campaign" label="Dapat menjalankan campaign" :checked="$storeLevel->can_run_campaign ?? false" />

        <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 dark:border-gray-800 sm:flex-row sm:justify-end">
            <x-idcore::button variant="outline" @click.prevent="
                if (formDirty) {
                    $confirm({
                        title: 'Perubahan Belum Disimpan',
                        message: 'Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman ini?',
                        confirmText: 'Ya, Tinggalkan',
                        cancelText: 'Batal',
                        variant: 'warning'
                    }).then(ok => { if (ok) window.location.href = '{{ route('master.store-level.index') }}'; });
                } else {
                    window.location.href = '{{ route('master.store-level.index') }}';
                }
            ">Batal</x-idcore::button>
            <x-idcore::button type="submit" variant="{{ $storeLevel ? 'warning' : 'success' }}">Simpan</x-idcore::button>
        </div>
    </form>
</x-idcore::card>
@endsection

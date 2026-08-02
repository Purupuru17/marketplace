@extends('idcore::layouts.backend')
@section('title', $category ? 'Edit Kategori' : 'Tambah Kategori')

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $category ? 'Edit Kategori' : 'Tambah Kategori' }}</h1>
        <x-idcore::breadcrumb :items="[['label' => 'Home', 'url' => route('dashboard')], ['label' => 'Master Data'], ['label' => 'Kategori', 'url' => route('master.category.index')], ['label' => $category ? 'Edit' : 'Create']]" />
    </div>
</div>

<x-idcore::card title="{{ $category ? 'Edit Kategori' : 'Tambah Kategori' }}" subtitle="Kategori untuk mengelompokkan produk." class="max-w-2xl">
    <form action="{{ $category ? route('master.category.update', $category->id) : route('master.category.store') }}" method="POST" class="space-y-6"
          x-data="{ formDirty: false }"
          @input.debounce.500ms="formDirty = true"
          @change="formDirty = true"
          @submit="formDirty = false"
          x-init="$watch('formDirty', val => { window.onbeforeunload = val ? () => true : null; })">
        @csrf
        @if($category) @method('PUT') @endif

        <x-idcore::select name="parent_id" label="Parent" :options="$parentOptions" :selected="$category->parent_id ?? null" placeholder="Tidak ada (kategori utama)" hint="Pilih untuk menjadikan sub-kategori." />

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-idcore::input name="name" label="Nama" required :value="$category->name ?? null" placeholder="Contoh: Elektronik" />
            <x-idcore::input name="sort_order" label="Urutan" type="number" min="0" :value="$category->sort_order ?? null" placeholder="0" />
            <x-idcore::select name="status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive']" :selected="$category->status ?? 'active'" required />
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
                    }).then(ok => { if (ok) window.location.href = '{{ route('master.category.index') }}'; });
                } else {
                    window.location.href = '{{ route('master.category.index') }}';
                }
            ">Batal</x-idcore::button>
            <x-idcore::button type="submit" variant="{{ $category ? 'warning' : 'success' }}">Simpan</x-idcore::button>
        </div>
    </form>
</x-idcore::card>
@endsection

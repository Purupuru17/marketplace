@extends('idcore::layouts.backend')
@section('title', $title)

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $title }}</h1>
        <x-idcore::breadcrumb :items="$breadcrumb" />
    </div>
</div>

<x-idcore::card title="{{ $subtitle }}" subtitle="{{ $title }}" class="max-w-4xl">
    <form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="space-y-6"
          x-data="{ formDirty: false }"
          @input.debounce.500ms="formDirty = true"
          @change="formDirty = true"
          @submit="formDirty = false"
          x-init="$watch('formDirty', val => { window.onbeforeunload = val ? () => true : null; })">
        @csrf
        @if($formData) @method('PUT') @endif

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-idcore::select name="store_id" label="Toko" :options="$storeOptions" :selected="$formData->store_id ?? null" placeholder="Pilih toko" required />
            <x-idcore::select name="category_id" label="Kategori" :options="$categoryOptions" :selected="$formData->category_id ?? null" placeholder="Tanpa kategori" />
            <x-idcore::input name="name" label="Nama Produk" required :value="$formData->name ?? null" placeholder="Contoh: Nasi Goreng Spesial" />
        </div>

        <x-idcore::textarea name="description" label="Deskripsi" :value="$formData->description ?? null" placeholder="Deskripsi produk" />

        @php
        $formImages = $formData?->images ?? collect();
        $generalImages = $formImages->whereNull('variant_id');
        $variantImages = $formImages->whereNotNull('variant_id');
    @endphp
        <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Gambar Produk</h3>
            <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Tandai satu gambar utama. Gambar yang dicentang hapus akan dihapus saat disimpan.</p>

            @if($generalImages->isNotEmpty())
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                    @foreach($generalImages as $image)
                        <div class="rounded-xl border border-gray-200 p-3 dark:border-gray-800">
                            <img src="{{ asset('storage/'.$image->path) }}" alt="Gambar produk" class="mb-2 h-50 w-full rounded-lg object-cover">
                            <div class="flex items-center justify-between gap-2 text-xs">
                                <label class="flex cursor-pointer items-center gap-1.5 text-gray-600 dark:text-gray-400">
                                    <input type="radio" name="primary_image" value="{{ $image->id }}" @checked($image->is_primary) class="rounded-full border-gray-300 text-brand-500">
                                    Utama
                                </label>
                                <label class="flex cursor-pointer items-center gap-1.5 text-error-600 dark:text-error-400">
                                    <input type="checkbox" name="remove_images[]" value="{{ $image->id }}" class="rounded border-gray-300 text-error-500">
                                    Hapus
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="mb-3 rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-500 dark:bg-gray-800/50 dark:text-gray-400">Belum ada gambar umum.</p>
            @endif

            @if($variantImages->isNotEmpty())
                <p class="p-2 text-xs font-medium text-gray-500 dark:text-gray-400">Gambar per varian (dikelola di form Varian Produk)</p>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                    @foreach($variantImages as $image)
                        <div class="rounded-xl border border-gray-200 p-3 dark:border-gray-800">
                            <img src="{{ asset('storage/'.$image->path) }}" alt="Gambar varian" class="mb-2 h-50 w-full rounded-lg object-cover">
                            <span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">
                                Varian: {{ $image->variant?->sku ?? '-' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
            
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2 mt-2">Tambah Gambar</h3>
            <x-idcore::file-input name="images[]" label="" accept="image/*" multiple
                hint="PNG/JPG/WebP, maks 2MB per file" />
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-idcore::select name="status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive']" :selected="$formData->status ?? 'active'" required />
            <div class="flex items-end pb-1">
                <x-idcore::toggle name="is_featured" label="Produk unggulan" :checked="$formData->is_featured ?? false" />
            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 dark:border-gray-800 sm:flex-row sm:justify-end">
            <x-idcore::button variant="light" @click.prevent="
                if (formDirty) {
                    $confirm({
                        title: 'Konfirmasi',
                        message: 'Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman ini ?',
                        confirmText: 'Ya, Tinggalkan',
                        cancelText: 'Batal',
                        variant: 'warning'
                    }).then(ok => { if (ok) window.location.href = '{{ route($module.'.index') }}'; });
                } else {
                    window.location.href = '{{ route($module.'.index') }}';
                }
            ">@svg('heroicon-o-arrow-path', 'h-4 w-4') Batal</x-idcore::button>
            <x-idcore::button type="submit" variant="{{ $formData ? 'warning' : 'success' }}">
                @svg('heroicon-o-paper-airplane', 'h-4 w-4') Simpan
            </x-idcore::button>
        </div>
    </form>
</x-idcore::card>
@endsection

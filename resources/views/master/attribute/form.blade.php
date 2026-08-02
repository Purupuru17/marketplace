@extends('idcore::layouts.backend')
@section('title', $attribute ? 'Edit Atribut' : 'Tambah Atribut')

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $attribute ? 'Edit Atribut' : 'Tambah Atribut' }}</h1>
        <x-idcore::breadcrumb :items="[['label' => 'Home', 'url' => route('dashboard')], ['label' => 'Master Data'], ['label' => 'Atribut', 'url' => route('master.attribute.index')], ['label' => $attribute ? 'Edit' : 'Create']]" />
    </div>
</div>

<x-idcore::card title="{{ $attribute ? 'Edit Atribut' : 'Tambah Atribut' }}" subtitle="Atribut digunakan sebagai dimensi varian produk (misal: warna, ukuran)." class="max-w-2xl">
    <form action="{{ $attribute ? route('master.attribute.update', $attribute->id) : route('master.attribute.store') }}" method="POST" class="space-y-6"
          x-data="{
              formDirty: false,
              values: @js($attribute?->values?->pluck('value')->toArray() ?? [])
          }"
          @input.debounce.500ms="formDirty = true"
          @change="formDirty = true"
          @submit="formDirty = false"
          x-init="$watch('formDirty', val => { window.onbeforeunload = val ? () => true : null; })">
        @csrf
        @if($attribute) @method('PUT') @endif

        <x-idcore::input name="name" label="Nama Atribut" required :value="$attribute->name ?? null" placeholder="Contoh: Warna" />

        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nilai Atribut</label>
            <div class="space-y-2">
                <template x-for="(value, index) in values" :key="index">
                    <div class="flex items-center gap-2">
                        <input type="text" name="values[]" x-model="values[index]"
                               placeholder="Contoh: Merah"
                               class="w-full rounded-lg border border-gray-200 px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs outline-none transition placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <x-idcore::button type="button" variant="outline-danger" size="xs" circle tooltip="Hapus" @click="values.splice(index, 1)">
                            @svg('heroicon-o-x-mark', 'h-3.5 w-3.5')
                        </x-idcore::button>
                    </div>
                </template>
            </div>
            <x-idcore::button type="button" variant="outline" class="mt-3" @click="values.push('')">
                @svg('heroicon-o-plus', 'h-4 w-4') Tambah Nilai
            </x-idcore::button>
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
                    }).then(ok => { if (ok) window.location.href = '{{ route('master.attribute.index') }}'; });
                } else {
                    window.location.href = '{{ route('master.attribute.index') }}';
                }
            ">Batal</x-idcore::button>
            <x-idcore::button type="submit" variant="{{ $attribute ? 'warning' : 'success' }}">Simpan</x-idcore::button>
        </div>
    </form>
</x-idcore::card>
@endsection

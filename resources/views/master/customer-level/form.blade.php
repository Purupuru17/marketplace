@extends('idcore::layouts.backend')
@section('title', $customerLevel ? 'Edit Customer Level' : 'Tambah Customer Level')

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $customerLevel ? 'Edit Customer Level' : 'Tambah Customer Level' }}</h1>
        <x-idcore::breadcrumb :items="[['label' => 'Home', 'url' => route('dashboard')], ['label' => 'Master Data'], ['label' => 'Customer Level', 'url' => route('master.customer-level.index')], ['label' => $customerLevel ? 'Edit' : 'Create']]" />
    </div>
</div>

<x-idcore::card title="{{ $customerLevel ? 'Edit Customer Level' : 'Tambah Customer Level' }}" subtitle="Tingkatan member berdasarkan akumulasi poin." class="max-w-2xl">
    <form action="{{ $customerLevel ? route('master.customer-level.update', $customerLevel->id) : route('master.customer-level.store') }}" method="POST" class="space-y-6"
          x-data="{ formDirty: false }"
          @input.debounce.500ms="formDirty = true"
          @change="formDirty = true"
          @submit="formDirty = false"
          x-init="$watch('formDirty', val => { window.onbeforeunload = val ? () => true : null; })">
        @csrf
        @if($customerLevel) @method('PUT') @endif

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-idcore::input name="name" label="Nama" required :value="$customerLevel->name ?? null" placeholder="Contoh: Silver" />
            <x-idcore::input name="minimum_points" label="Min Poin" type="number" min="0" required :value="$customerLevel->minimum_points ?? null" placeholder="0" />
            <x-idcore::input name="sort_order" label="Urutan" type="number" min="0" :value="$customerLevel->sort_order ?? null" placeholder="0" />
            <x-idcore::select name="status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive']" :selected="$customerLevel->status ?? 'active'" required />
        </div>

        <x-idcore::textarea name="benefit" label="Benefit" :value="$customerLevel->benefit ?? null" placeholder="Deskripsi benefit level" />

        <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 dark:border-gray-800 sm:flex-row sm:justify-end">
            <x-idcore::button variant="outline" @click.prevent="
                if (formDirty) {
                    $confirm({
                        title: 'Perubahan Belum Disimpan',
                        message: 'Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman ini?',
                        confirmText: 'Ya, Tinggalkan',
                        cancelText: 'Batal',
                        variant: 'warning'
                    }).then(ok => { if (ok) window.location.href = '{{ route('master.customer-level.index') }}'; });
                } else {
                    window.location.href = '{{ route('master.customer-level.index') }}';
                }
            ">Batal</x-idcore::button>
            <x-idcore::button type="submit" variant="{{ $customerLevel ? 'warning' : 'success' }}">Simpan</x-idcore::button>
        </div>
    </form>
</x-idcore::card>
@endsection

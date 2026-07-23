@extends('idcore::layouts.backend')
@section('title', $group ? 'Edit Grup' : 'Tambah Grup')

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $group ? 'Edit Grup' : 'Tambah Grup' }}</h1>
        <x-idcore::breadcrumb :items="[['label' => 'Home', 'url' => route('dashboard')], ['label' => 'Group', 'url' => route('sistem.group.index')], ['label' => $group ? 'Edit' : 'Create']]" />
    </div>
</div>

<x-idcore::card title="{{ $group ? 'Edit Grup' : 'Tambah Grup' }}" subtitle="Role dipakai untuk mengelompokkan permission dan context switching user." class="max-w-2xl">
    <form action="{{ $group ? route('sistem.group.update', $group->id) : route('sistem.group.store') }}" method="POST" class="space-y-6"
          x-data="{ formDirty: false }"
          @input.debounce.500ms="formDirty = true"
          @change="formDirty = true"
          @submit="formDirty = false"
          x-init="$watch('formDirty', val => { window.onbeforeunload = val ? () => true : null; })">
        @csrf
        @if($group) @method('PUT') @endif

        <x-idcore::input name="name" label="Nama Grup" required :value="$group->name ?? null" placeholder="Contoh: Dosen" />

        <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 dark:border-gray-800 sm:flex-row sm:justify-end">
            <x-idcore::button variant="outline" @click.prevent="
                if (formDirty) {
                    $confirm({
                        title: 'Perubahan Belum Disimpan',
                        message: 'Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman ini?',
                        confirmText: 'Ya, Tinggalkan',
                        cancelText: 'Batal',
                        variant: 'warning'
                    }).then(ok => { if (ok) window.location.href = '{{ route('sistem.group.index') }}'; });
                } else {
                    window.location.href = '{{ route('sistem.group.index') }}';
                }
            ">Batal</x-idcore::button>
            <x-idcore::button type="submit" variant="primary">Simpan</x-idcore::button>
        </div>
    </form>
</x-idcore::card>
@endsection

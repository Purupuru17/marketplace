@extends('idcore::layouts.backend')
@section('title', $locationNode ? 'Edit Node Lokasi' : 'Tambah Node Lokasi')

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $locationNode ? 'Edit Node Lokasi' : 'Tambah Node Lokasi' }}</h1>
        <x-idcore::breadcrumb :items="[['label' => 'Home', 'url' => route('dashboard')], ['label' => 'Master Data'], ['label' => 'Node Lokasi', 'url' => route('master.location-node.index')], ['label' => $locationNode ? 'Edit' : 'Create']]" />
    </div>
</div>

<x-idcore::card title="{{ $locationNode ? 'Edit Node Lokasi' : 'Tambah Node Lokasi' }}" subtitle="Koordinat opsional, nama node yang akan dipakai untuk perhitungan jarak." class="max-w-2xl">
    <form action="{{ $locationNode ? route('master.location-node.update', $locationNode->id) : route('master.location-node.store') }}" method="POST" class="space-y-6"
          x-data="{ formDirty: false }"
          @input.debounce.500ms="formDirty = true"
          @change="formDirty = true"
          @submit="formDirty = false"
          x-init="$watch('formDirty', val => { window.onbeforeunload = val ? () => true : null; })">
        @csrf
        @if($locationNode) @method('PUT') @endif

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-idcore::input name="name" label="Nama" required :value="$locationNode->name ?? null" placeholder="Contoh: Kota Bandung" />
            <x-idcore::select name="status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive']" :selected="$locationNode->status ?? 'active'" required />
            <x-idcore::input name="lat" label="Latitude" type="number" step="any" :value="$locationNode->lat ?? null" placeholder="-6.9175" />
            <x-idcore::input name="lng" label="Longitude" type="number" step="any" :value="$locationNode->lng ?? null" placeholder="107.6191" />
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
                    }).then(ok => { if (ok) window.location.href = '{{ route('master.location-node.index') }}'; });
                } else {
                    window.location.href = '{{ route('master.location-node.index') }}';
                }
            ">Batal</x-idcore::button>
            <x-idcore::button type="submit" variant="{{ $locationNode ? 'warning' : 'success' }}">Simpan</x-idcore::button>
        </div>
    </form>
</x-idcore::card>
@endsection

@extends('idcore::layouts.backend')
@section('title', $locationDistance ? 'Edit Jarak Antar Node' : 'Tambah Jarak Antar Node')

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $locationDistance ? 'Edit Jarak Antar Node' : 'Tambah Jarak Antar Node' }}</h1>
        <x-idcore::breadcrumb :items="[['label' => 'Home', 'url' => route('dashboard')], ['label' => 'Master Data'], ['label' => 'Jarak Antar Node', 'url' => route('master.location-distance.index')], ['label' => $locationDistance ? 'Edit' : 'Create']]" />
    </div>
</div>

<x-idcore::card title="{{ $locationDistance ? 'Edit Jarak Antar Node' : 'Tambah Jarak Antar Node' }}" subtitle="Grafik bersifat dua arah, cukup satu baris per pasangan node." class="max-w-2xl">
    <form action="{{ $locationDistance ? route('master.location-distance.update', $locationDistance->id) : route('master.location-distance.store') }}" method="POST" class="space-y-6"
          x-data="{ formDirty: false }"
          @input.debounce.500ms="formDirty = true"
          @change="formDirty = true"
          @submit="formDirty = false"
          x-init="$watch('formDirty', val => { window.onbeforeunload = val ? () => true : null; })">
        @csrf
        @if($locationDistance) @method('PUT') @endif

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-idcore::select name="origin_node_id" label="Node Asal" :options="$nodeOptions" :selected="$locationDistance->origin_node_id ?? null" placeholder="Pilih node" required />
            <x-idcore::select name="destination_node_id" label="Node Tujuan" :options="$nodeOptions" :selected="$locationDistance->destination_node_id ?? null" placeholder="Pilih node" required hint="Tidak boleh sama dengan node asal." />
        </div>

        <x-idcore::input name="distance_km" label="Jarak (km)" type="number" step="0.01" min="0.01" required :value="$locationDistance->distance_km ?? null" placeholder="0.00" />

        <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 dark:border-gray-800 sm:flex-row sm:justify-end">
            <x-idcore::button variant="outline" @click.prevent="
                if (formDirty) {
                    $confirm({
                        title: 'Perubahan Belum Disimpan',
                        message: 'Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman ini?',
                        confirmText: 'Ya, Tinggalkan',
                        cancelText: 'Batal',
                        variant: 'warning'
                    }).then(ok => { if (ok) window.location.href = '{{ route('master.location-distance.index') }}'; });
                } else {
                    window.location.href = '{{ route('master.location-distance.index') }}';
                }
            ">Batal</x-idcore::button>
            <x-idcore::button type="submit" variant="{{ $locationDistance ? 'warning' : 'success' }}">Simpan</x-idcore::button>
        </div>
    </form>
</x-idcore::card>
@endsection

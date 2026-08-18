@extends('idcore::layouts.backend')
@section('title', $title)

@php
    $days = [
        'monday' => 'Senin',
        'tuesday' => 'Selasa',
        'wednesday' => 'Rabu',
        'thursday' => 'Kamis',
        'friday' => 'Jumat',
        'saturday' => 'Sabtu',
        'sunday' => 'Minggu',
    ];
@endphp

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
            <x-idcore::select name="user_id" label="Pemilik (User)" :options="$userOptions" :selected="$formData->user_id ?? null" placeholder="Pilih pemilik" required />
            <x-idcore::select name="store_level_id" label="Level Toko" :options="$levelOptions" :selected="$formData->store_level_id ?? null" placeholder="Pilih level" />
            <x-idcore::select name="location_node_id" label="Node Lokasi" :options="$nodeOptions" :selected="$formData->location_node_id ?? null" placeholder="Pilih node" />
            <x-idcore::input name="store_name" label="Nama Toko" required :value="$formData->store_name ?? null" placeholder="Contoh: Toko Berkah" />
            <x-idcore::input name="phone" label="Telepon" :value="$formData->phone ?? null" placeholder="08xxxxxxxxxx" />
            <x-idcore::input name="email" label="Email Toko" type="email" :value="$formData->email ?? null" placeholder="toko@email.com" />
            <x-idcore::input name="lat" label="Latitude" type="number" step="any" :value="$formData->lat ?? null" placeholder="-6.9175" />
            <x-idcore::input name="lng" label="Longitude" type="number" step="any" :value="$formData->lng ?? null" placeholder="107.6191" />
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-idcore::file-input name="logo" label="Logo Toko" accept="image/*" hint="PNG/JPG/WebP, persegi min 240x240, maks 2MB"
                :preview="($formData?->logo ?? null) ? asset('storage/'.$formData->logo) : null" />
            <x-idcore::file-input name="banner" label="Banner Toko" accept="image/*" hint="PNG/JPG/WebP, min 1024x768, maks 2MB"
                :preview="($formData?->banner ?? null) ? asset('storage/'.$formData->banner) : null" />
        </div>

        <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Rekening Bank Toko</h3>
            <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Ditampilkan ke customer saat memilih Transfer Bank Manual.</p>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <x-idcore::input name="bank_name" label="Nama Bank" :value="$formData->bank_name ?? null" placeholder="Contoh: Bank Simulasi" />
                <x-idcore::input name="account_number" label="No. Rekening" :value="$formData->account_number ?? null" placeholder="Nomor rekening toko" />
                <x-idcore::input name="account_name" label="Atas Nama" :value="$formData->account_name ?? null" placeholder="Nama pemilik rekening" />
            </div>
        </div>

        <x-idcore::textarea name="description" label="Deskripsi" :value="$formData->description ?? null" placeholder="Deskripsi toko" />

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
            <x-idcore::input name="rate_per_km" label="Tarif per km (Rp)" type="number" step="0.01" min="0" required :value="$formData->rate_per_km ?? 0" placeholder="0" />
            <x-idcore::input name="min_free_distance_km" label="Min jarak gratis ongkir (km)" type="number" step="0.01" min="0" required :value="$formData->min_free_distance_km ?? 0" placeholder="0" />
            <x-idcore::input name="max_radius_km" label="Maks radius pengiriman (km)" type="number" step="0.01" min="0" :value="$formData->max_radius_km ?? null" placeholder="Kosongkan jika tidak terbatas" />
        </div>

        <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Jam Operasional</h3>
            <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Setel jam buka/tutup setiap hari. Matikan bila toko tutup di hari tersebut.</p>
            <div class="space-y-3 rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                @foreach($days as $day => $label)
                    <div class="grid grid-cols-12 items-center gap-3">
                        <div class="col-span-3 text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</div>
                        <div class="col-span-3 flex items-center gap-2">
                            <input type="hidden" name="hours[{{ $day }}][is_open]" value="0">
                            <input type="checkbox" name="hours[{{ $day }}][is_open]" value="1"
                                   id="hours-{{ $day }}-is-open"
                                   @checked(old('hours.'.$day.'.is_open', $hoursByDay[$day]->is_open ?? true))
                                   class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900">
                            <label for="hours-{{ $day }}-is-open" class="text-sm text-gray-600 dark:text-gray-400">Buka</label>
                        </div>
                        <div class="col-span-3">
                            <x-idcore::input type="time" name="hours[{{ $day }}][opens_at]" :value="$hoursByDay[$day]->opens_at ?? null" />
                        </div>
                        <div class="col-span-3">
                            <x-idcore::input type="time" name="hours[{{ $day }}][closes_at]" :value="$hoursByDay[$day]->closes_at ?? null" />
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <x-idcore::select name="status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive']" :selected="$formData->status ?? 'active'" required />

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

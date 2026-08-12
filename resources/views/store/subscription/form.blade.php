@extends('idcore::layouts.backend')
@section('title', $title)

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $title }}</h1>
        <x-idcore::breadcrumb :items="$breadcrumb" />
    </div>
</div>

<x-idcore::card title="{{ $subtitle }}" subtitle="{{ $title }}" class="max-w-2xl">
    <form action="{{ $action }}" method="POST" class="space-y-6"
          x-data="{ formDirty: false }"
          @input.debounce.500ms="formDirty = true"
          @change="formDirty = true"
          @submit="formDirty = false"
          x-init="$watch('formDirty', val => { window.onbeforeunload = val ? () => true : null; })">
        @csrf
        @if($formData) @method('PUT') @endif

        <x-idcore::select name="store_id" label="Toko" :options="$storeOptions" :selected="$formData->store_id ?? null" placeholder="Pilih toko" required />

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-idcore::select name="store_level_id" label="Level" :options="$levelOptions" :selected="$formData->store_level_id ?? null" placeholder="Pilih level" required />
            <x-idcore::select name="status" label="Status" :options="['active' => 'Active', 'expired' => 'Expired', 'cancelled' => 'Cancelled']" :selected="$formData->status ?? 'active'" required />
            <x-idcore::input name="starts_at" label="Mulai" type="date" required :value="$formData?->starts_at?->format('Y-m-d') ?? null" />
            <x-idcore::input name="ends_at" label="Berakhir" type="date" required :value="$formData?->ends_at?->format('Y-m-d') ?? null" />
        </div>

        <x-idcore::toggle name="auto_renew" label="Perpanjang otomatis" :checked="$formData->auto_renew ?? false" />

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

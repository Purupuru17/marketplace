@extends('idcore::layouts.backend')
@section('title', $subscription ? 'Edit Subscription' : 'Tambah Subscription')

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $subscription ? 'Edit Subscription' : 'Tambah Subscription' }}</h1>
        <x-idcore::breadcrumb :items="[['label' => 'Home', 'url' => route('dashboard')], ['label' => 'Toko'], ['label' => 'Subscription', 'url' => route('toko.subscription.index')], ['label' => $subscription ? 'Edit' : 'Create']]" />
    </div>
</div>

<x-idcore::card title="{{ $subscription ? 'Edit Subscription' : 'Tambah Subscription' }}" subtitle="{{ $subscription ? 'Perubahan level akan menyesuaikan invoice pending.' : 'Invoice otomatis dibuat saat subscription dibuat.' }}" class="max-w-2xl">
    <form action="{{ $subscription ? route('toko.subscription.update', $subscription->id) : route('toko.subscription.store') }}" method="POST" class="space-y-6"
          x-data="{ formDirty: false }"
          @input.debounce.500ms="formDirty = true"
          @change="formDirty = true"
          @submit="formDirty = false"
          x-init="$watch('formDirty', val => { window.onbeforeunload = val ? () => true : null; })">
        @csrf
        @if($subscription) @method('PUT') @endif

        <x-idcore::select name="store_id" label="Toko" :options="$storeOptions" :selected="$subscription->store_id ?? null" placeholder="Pilih toko" required />

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-idcore::select name="store_level_id" label="Level" :options="$levelOptions" :selected="$subscription->store_level_id ?? null" placeholder="Pilih level" required />
            <x-idcore::select name="status" label="Status" :options="['active' => 'Active', 'expired' => 'Expired', 'cancelled' => 'Cancelled']" :selected="$subscription->status ?? 'active'" required />
            <x-idcore::input name="starts_at" label="Mulai" type="date" required :value="$subscription?->starts_at?->format('Y-m-d') ?? null" />
            <x-idcore::input name="ends_at" label="Berakhir" type="date" required :value="$subscription?->ends_at?->format('Y-m-d') ?? null" />
        </div>

        <x-idcore::toggle name="auto_renew" label="Perpanjang otomatis" :checked="$subscription->auto_renew ?? false" />

        <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 dark:border-gray-800 sm:flex-row sm:justify-end">
            <x-idcore::button variant="outline" @click.prevent="
                if (formDirty) {
                    $confirm({
                        title: 'Perubahan Belum Disimpan',
                        message: 'Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman ini?',
                        confirmText: 'Ya, Tinggalkan',
                        cancelText: 'Batal',
                        variant: 'warning'
                    }).then(ok => { if (ok) window.location.href = '{{ route('toko.subscription.index') }}'; });
                } else {
                    window.location.href = '{{ route('toko.subscription.index') }}';
                }
            ">Batal</x-idcore::button>
            <x-idcore::button type="submit" variant="{{ $subscription ? 'warning' : 'success' }}">Simpan</x-idcore::button>
        </div>
    </form>
</x-idcore::card>
@endsection

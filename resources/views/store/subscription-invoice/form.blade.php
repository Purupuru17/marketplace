@extends('idcore::layouts.backend')
@section('title', $invoice ? 'Edit Invoice Subscription' : 'Tambah Invoice Subscription')

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $invoice ? 'Edit Invoice Subscription' : 'Tambah Invoice Subscription' }}</h1>
        <x-idcore::breadcrumb :items="[['label' => 'Home', 'url' => route('dashboard')], ['label' => 'Toko'], ['label' => 'Invoice Subscription', 'url' => route('toko.subscription-invoice.index')], ['label' => $invoice ? 'Edit' : 'Create']]" />
    </div>
</div>

<x-idcore::card title="{{ $invoice ? 'Edit Invoice Subscription' : 'Tambah Invoice Subscription' }}" subtitle="{{ $invoice ? '' : 'Nomor invoice dibuat otomatis saat disimpan.' }}" class="max-w-2xl">
    <form action="{{ $invoice ? route('toko.subscription-invoice.update', $invoice->id) : route('toko.subscription-invoice.store') }}" method="POST" class="space-y-6"
          x-data="{ formDirty: false }"
          @input.debounce.500ms="formDirty = true"
          @change="formDirty = true"
          @submit="formDirty = false"
          x-init="$watch('formDirty', val => { window.onbeforeunload = val ? () => true : null; })">
        @csrf
        @if($invoice) @method('PUT') @endif

        @if($invoice)
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">No Invoice</label>
                <input type="text" value="{{ $invoice->invoice_no }}" readonly
                       class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
            </div>
        @endif

        <x-idcore::select name="subscription_id" label="Subscription" :options="$subscriptionOptions" :selected="$invoice->subscription_id ?? null" placeholder="Pilih subscription" required />

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-idcore::input name="amount" label="Jumlah (Rp)" type="number" step="0.01" min="0" required :value="$invoice->amount ?? null" placeholder="0" />
            <x-idcore::input name="due_at" label="Jatuh Tempo" type="date" required :value="$invoice?->due_at?->format('Y-m-d') ?? null" />
            <x-idcore::select name="status" label="Status" :options="['pending' => 'Pending', 'paid' => 'Paid', 'overdue' => 'Overdue']" :selected="$invoice->status ?? 'pending'" required hint="Status paid otomatis mengisi tanggal pembayaran." />
        </div>

        @if($invoice && $invoice->paid_at)
            <p class="text-sm text-gray-500 dark:text-gray-400">Dibayar pada: {{ $invoice->paid_at->format('d M Y H:i') }}</p>
        @endif

        <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 dark:border-gray-800 sm:flex-row sm:justify-end">
            <x-idcore::button variant="outline" @click.prevent="
                if (formDirty) {
                    $confirm({
                        title: 'Perubahan Belum Disimpan',
                        message: 'Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman ini?',
                        confirmText: 'Ya, Tinggalkan',
                        cancelText: 'Batal',
                        variant: 'warning'
                    }).then(ok => { if (ok) window.location.href = '{{ route('toko.subscription-invoice.index') }}'; });
                } else {
                    window.location.href = '{{ route('toko.subscription-invoice.index') }}';
                }
            ">Batal</x-idcore::button>
            <x-idcore::button type="submit" variant="{{ $invoice ? 'warning' : 'success' }}">Simpan</x-idcore::button>
        </div>
    </form>
</x-idcore::card>
@endsection

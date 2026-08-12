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

        @if($formData)
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">No Invoice</label>
                <input type="text" value="{{ $formData->invoice_no }}" readonly
                       class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
            </div>
        @endif

        <x-idcore::select name="subscription_id" label="Subscription" :options="$subscriptionOptions" :selected="$formData->subscription_id ?? null" placeholder="Pilih subscription" required />

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-idcore::input name="amount" label="Jumlah (Rp)" type="number" step="0.01" min="0" required :value="$formData->amount ?? null" placeholder="0" />
            <x-idcore::input name="due_at" label="Jatuh Tempo" type="date" required :value="$formData?->due_at?->format('Y-m-d') ?? null" />
            <x-idcore::select name="status" label="Status" :options="['pending' => 'Pending', 'paid' => 'Paid', 'overdue' => 'Overdue']" :selected="$formData->status ?? 'pending'" required hint="Status paid otomatis mengisi tanggal pembayaran." />
        </div>

        @if($formData && $formData->paid_at)
            <p class="text-sm text-gray-500 dark:text-gray-400">Dibayar pada: {{ $formData->paid_at->format('d M Y H:i') }}</p>
        @endif

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

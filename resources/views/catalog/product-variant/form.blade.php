@extends('idcore::layouts.backend')
@section('title', $title)

@php
    $selectedAttributeValueIds = $formData ? $formData->attributeValues->pluck('id')->all() : [];
@endphp

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $title }}</h1>
        <x-idcore::breadcrumb :items="$breadcrumb" />
    </div>
</div>

<x-idcore::card title="{{ $subtitle }}" subtitle="{{ $title }}" class="max-w-4xl">
    <form action="{{ $action }}" method="POST" class="space-y-6"
          x-data="{ formDirty: false }"
          @input.debounce.500ms="formDirty = true"
          @change="formDirty = true"
          @submit="formDirty = false"
          x-init="$watch('formDirty', val => { window.onbeforeunload = val ? () => true : null; })">
        @csrf
        @if($formData) @method('PUT') @endif

        <x-idcore::select name="product_id" label="Produk" :options="$productOptions" :selected="$formData->product_id ?? null" placeholder="Pilih produk" required />

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
            <x-idcore::input name="sku" label="SKU" required :value="$formData->sku ?? null" placeholder="Contoh: NGS-REG-500" />
            <x-idcore::input name="price" label="Harga (Rp)" type="number" step="0.01" min="0" required :value="$formData->price ?? null" placeholder="0" />
            <x-idcore::input name="stock" label="Stok" type="number" min="0" required :value="$formData->stock ?? 0" placeholder="0" />
            <x-idcore::input name="weight_grams" label="Berat (gram)" type="number" min="0" required :value="$formData->weight_grams ?? 0" placeholder="0" />
            <x-idcore::select name="status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive']" :selected="$formData->status ?? 'active'" required />
        </div>

        @if(! empty($attributeGroups))
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Atribut</h3>
                <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Kombinasi atribut mendefinisikan varian ini. Maksimal satu nilai per atribut.</p>
                <div class="space-y-4 rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                    @foreach($attributeGroups as $groupName => $values)
                        <div>
                            <div class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">{{ $groupName }}</div>
                            <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                                @foreach($values as $valueId => $value)
                                    <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                        <input type="checkbox" name="attribute_value_ids[]" value="{{ $valueId }}"
                                               @checked(in_array($valueId, old('attribute_value_ids', $selectedAttributeValueIds)))
                                               class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900">
                                        {{ $value }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                @error('attribute_value_ids')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
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

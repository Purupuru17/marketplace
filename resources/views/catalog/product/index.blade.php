@extends('idcore::layouts.backend')
@section('title', $title)

@section('content')
@php
    $rows = $listData->getCollection()->map(fn ($item) => [
        'id' => $item->id,
        'name_plain' => $item->name,
        'product' => '<div><p class="font-semibold text-gray-900 dark:text-white">'.e($item->name).'</p><p class="text-xs text-gray-500 dark:text-gray-400">/'.e($item->slug).'</p></div>',
        'store' => e($item->store->store_name ?? '-'),
        'category' => $item->category ? '<span class="inline-flex rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">'.e($item->category->name).'</span>' : '<span class="text-gray-400">-</span>',
        'status' => $item->status === 'active' ? '<span class="inline-flex rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-600 dark:bg-green-500/10 dark:text-green-400">Active</span>' : '<span class="inline-flex rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-600 dark:bg-red-500/10 dark:text-red-400">Inactive</span>',
        'edit_url' => auth()->user()->can($rolesName.'.edit') ? route($module.'.edit', $item->id) : null,
        'delete_url' => auth()->user()->can($rolesName.'.delete') ? route($module.'.destroy', $item->id) : null,
    ])->values()->all();
    $columns = [
        ['key' => 'product', 'label' => 'Produk', 'html' => true],
        ['key' => 'store', 'label' => 'Toko'],
        ['key' => 'category', 'label' => 'Kategori', 'html' => true],
        ['key' => 'status', 'label' => 'Status', 'html' => true, 'align' => 'center'],
    ];
@endphp
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $title }}</h1>
        <x-idcore::breadcrumb :items="$breadcrumb" />
    </div>
    @can($rolesName.'.create')
        <x-idcore::button variant="primary" :href="route($module.'.create')">@svg('heroicon-o-pencil', 'h-4 w-4') Tambah Data</x-idcore::button>
    @endcan
</div>
<x-idcore::card title="{{ $subtitle }}" subtitle="{{ $title }}" :padding="false">
    <x-idcore::datatable :columns="$columns" :rows="$rows" :show-number="true" searchable embedded>
        <x-slot:actions><x-idcore::partials.dt-actions :module="$module" :roles-name="$rolesName" /></x-slot:actions>
    </x-idcore::datatable>
</x-idcore::card>
@endsection
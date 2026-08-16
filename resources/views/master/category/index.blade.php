@extends('idcore::layouts.backend')
@section('title', $title)

@section('content')
<x-idcore::page-header :title="$title" :subtitle="$subtitle" :breadcrumb="$breadcrumb">
    <x-slot:actions>
        @can($rolesName.'.create')
            <x-idcore::button variant="primary" :href="route($module.'.create')">
                @svg('heroicon-o-pencil', 'h-4 w-4') Tambah Data
            </x-idcore::button>
        @endcan
    </x-slot:actions>
</x-idcore::page-header>

<x-idcore::card title="{{ $subtitle }}" subtitle="{{ $title }}" :padding="false">
    <x-idcore::datatable-server
        :url="route($module.'.ajax', ['type' => 'table', 'source' => 'index'])"
        :columns="$columns">

        <x-slot:filters>
            <div>
                <x-idcore::select
                    name="filter_parent_id"
                    label="Parent"
                    x-model="pendingFilters.parent_id"
                    :options="$parentOptions"
                    placeholder="Semua Kategori"
                />
            </div>

            <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-4">
                <button type="button" @click="applyFilters()"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-brand-300 px-4 py-2 text-sm font-medium text-brand-600 hover:bg-brand-50 dark:border-brand-800 dark:text-brand-400 dark:hover:bg-brand-500/10">
                    @svg('heroicon-o-magnifying-glass', 'h-4 w-4')
                    Pencarian
                </button>
                <button type="button" @click="pendingFilters = {}; applyFilters()"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800">
                    Reset
                </button>
            </div>
        </x-slot:filters>

        <x-slot:actions>
            <x-idcore::partials.dt-actions :module="$module" :roles-name="$rolesName" />
        </x-slot:actions>
    </x-idcore::datatable-server>
</x-idcore::card>
@endsection

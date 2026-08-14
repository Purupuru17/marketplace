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
    <x-idcore::datatable :columns="$columns" :rows="$rows" :show-number="true" searchable embedded>
        <x-slot:actions>
            <x-idcore::partials.dt-actions :module="$module" :roles-name="$rolesName" />
        </x-slot:actions>
    </x-idcore::datatable>
</x-idcore::card>
@endsection
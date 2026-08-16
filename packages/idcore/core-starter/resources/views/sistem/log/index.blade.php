@extends('idcore::layouts.backend')
@section('title', $title)

@section('content')
<x-idcore::page-header :title="$title" :subtitle="$subtitle" :breadcrumb="$breadcrumb" />

<x-idcore::card title="{{ $subtitle }}" subtitle="{{ $title }}" :padding="false">
    <x-idcore::datatable-server
        :url="route($module.'.ajax', ['type' => 'table', 'source' => 'index'])"
        :columns="$columns">

        <x-slot:actions>
            <x-idcore::partials.dt-actions :module="$module" :roles-name="$rolesName" />
        </x-slot:actions>
    </x-idcore::datatable-server>
</x-idcore::card>
@endsection
@extends('idcore::layouts.backend')
@section('title', $title)

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $title }}</h1>
        <x-idcore::breadcrumb :items="$breadcrumb" />
    </div>
    @can($rolesName.'.create')
        <x-idcore::button variant="primary" :href="route($module.'.create')">
            @svg('heroicon-o-pencil', 'h-4 w-4') Tambah Data
        </x-idcore::button>
    @endcan
</div>

<x-idcore::card title="{{ $subtitle }}" subtitle="{{ $title }}" :padding="false">
    <form method="GET" action="{{ url()->current() }}" class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
            <span>Show</span>
            <x-idcore::select name="per_page" :options="['All' => 'All', '10' => '10', '8' => '8', '5' => '5']" :selected="request('per_page', 'All')" placeholder="" onchange="this.form.submit()" />
            <span>entries</span>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <div class="relative w-full sm:w-72">
                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400">@svg('heroicon-o-magnifying-glass', 'h-4 w-4')</span>
                <x-idcore::input name="search" type="search" value="{{ request('search') }}" placeholder="Search..." />
            </div>
            <x-idcore::button variant="outline" size="sm">
                @svg('heroicon-o-arrow-down-tray', 'h-4 w-4') Download
            </x-idcore::button>
        </div>
    </form>

    <x-idcore::table>
        <thead class="bg-gray-50 dark:bg-gray-800/50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Nama Menu</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 hidden md:table-cell">URL</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 hidden lg:table-cell">Actions</th>
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($tree as $item)
                @include('idcore::sistem.menu.partials.tree-item', ['items' => [$item], 'depth' => 0])
            @empty
                <x-idcore::table-empty colspan="5" message="Data tidak ditemukan." />
            @endforelse
        </tbody>
    </x-idcore::table>
</x-idcore::card>
@endsection

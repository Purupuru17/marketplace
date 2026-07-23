@extends('idcore::layouts.backend')
@section('title', 'Kelola Menu')

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Kelola Menu</h1>
        <x-idcore::breadcrumb :items="[['label' => 'Home', 'url' => route('dashboard')], ['label' => 'Menu']]" />
    </div>
    @can('menu.create')
        <x-idcore::button variant="primary" :href="route('sistem.menu.create')">Tambah Menu</x-idcore::button>
    @endcan
</div>

<x-idcore::card title="Datatable Menu" subtitle="Struktur menu sidebar dan action permission yang digenerate" :padding="false">
    <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
            <span>Show</span>
            <select class="h-9 rounded-lg border-gray-200 bg-white text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <option>All</option>
                <option>10</option>
                <option>8</option>
                <option>5</option>
            </select>
            <span>entries</span>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <div class="relative w-full sm:w-72">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">@svg('heroicon-o-magnifying-glass', 'h-4 w-4')</span>
                <input type="search" placeholder="Search..." class="h-10 w-full rounded-lg border border-gray-200 bg-white pl-10 pr-3 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            </div>
            <button type="button" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">
                @svg('heroicon-o-arrow-down-tray', 'h-4 w-4 text-gray-500') Download
            </button>
        </div>
    </div>

    <x-idcore::table>
        <thead class="bg-gray-50 dark:bg-gray-800/50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Nama Menu</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 hidden md:table-cell">URL</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 hidden lg:table-cell">Actions</th>
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($tree as $item)
                @include('idcore::sistem.menu.partials.tree-item', ['items' => [$item], 'depth' => 0])
            @empty
                <x-idcore::table-empty colspan="5" message="Belum ada data menu." />
            @endforelse
        </tbody>
    </x-idcore::table>
</x-idcore::card>
@endsection

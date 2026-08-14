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
            <x-idcore::select name="per_page" :options="[10 => '10', 25 => '25', 50 => '50']" :selected="request('per_page', 10)" placeholder="" onchange="this.form.submit()" />
            <span>entries</span>
        </div>
        <div class="w-full md:max-w-xs">
            <x-idcore::input name="search" type="search" icon="magnifying-glass" value="{{ request('search') }}" placeholder="Search..." />
        </div>
    </form>

    <x-idcore::table>
        <thead class="bg-gray-50 dark:bg-gray-800/50">
            <tr>
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">No</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Nama</th>
                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Min Poin</th>
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Benefit</th>
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($listData as $item)
                <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/60">
                    <td class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">{{ $listData->firstItem() + $loop->index }}</td>
                    <td class="px-6 py-4">
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $item->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Urutan: {{ $item->sort_order }}</p>
                    </td>
                    <td class="px-6 py-4 text-right text-gray-700 dark:text-gray-300">{{ number_format($item->minimum_points, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">{{ Str::limit($item->benefit ?? '-', 40) }}</td>
                    <td class="px-6 py-4 text-center">
                        @if($item->status === 'active')
                            <x-idcore::badge variant="green">Active</x-idcore::badge>
                        @else
                            <x-idcore::badge variant="red">Inactive</x-idcore::badge>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-1">
                            @can($rolesName.'.edit')
                                <x-idcore::partials.edit-button :module="$module" :id="$item->id" />
                            @endcan
                            @can($rolesName.'.delete')
                                <x-idcore::partials.delete-button :module="$module" :id="$item->id" :name="$item->name" />
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <x-idcore::table-empty colspan="6" message="Belum ada data customer level." />
            @endforelse
        </tbody>
    </x-idcore::table>

    <x-idcore::pagination :paginator="$listData" />
</x-idcore::card>
@endsection

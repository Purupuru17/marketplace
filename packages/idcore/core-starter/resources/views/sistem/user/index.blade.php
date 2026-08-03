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
        <div class="relative w-full md:max-w-xs">
            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400">@svg('heroicon-o-magnifying-glass', 'h-4 w-4')</span>
            <x-idcore::input name="search" type="search" value="{{ request('search') }}" placeholder="Search..." />
        </div>
    </form>
    
    <x-idcore::table>
        <thead class="bg-gray-50 dark:bg-gray-800/50">
            <tr>
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">No</th>
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Name</th>
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Email</th>
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Role</th>
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($listData as $item)
                <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/60">
                    <td class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                        {{ $listData->firstItem() + $loop->index }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center gap-3">
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $item->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">ID : {{ $item->id }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center text-gray-600 dark:text-gray-300">{{ $item->email }}</td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex flex-wrap gap-1.5">
                            @forelse($item->roles as $role)
                                <x-idcore::badge variant="indigo">{{ $role->name }}</x-idcore::badge>
                            @empty
                                <x-idcore::badge>Tanpa role</x-idcore::badge>
                            @endforelse
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-end gap-1">
                            @can($rolesName.'.edit')
                                <x-idcore::partials.edit-button :module="$module" :id="$item->id">
                                </x-idcore::partials.edit-button>
                            @endcan
                            @can($rolesName.'.delete')
                                <x-idcore::partials.delete-button :module="$module" :id="$item->id" :name="$item->name">
                                </x-idcore::partials.delete-button>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <x-idcore::table-empty colspan="4" message="Data tidak ditemukan." />
            @endforelse
        </tbody>
    </x-idcore::table>

    <x-idcore::pagination :paginator="$listData" />
</x-idcore::card>
@endsection

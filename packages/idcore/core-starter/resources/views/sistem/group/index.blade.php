@extends('idcore::layouts.backend')
@section('title', 'Kelola Grup / Role')

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Kelola Grup / Role</h1>
        <x-idcore::breadcrumb :items="[['label' => 'Home', 'url' => route('dashboard')], ['label' => 'Group']]" />
    </div>
    @can('group.create')
        <x-idcore::button variant="primary" :href="route('sistem.group.create')">Tambah Grup</x-idcore::button>
    @endcan
</div>

<x-idcore::card title="Datatable Grup" subtitle="Daftar role yang dapat diberi permission" :padding="false">
    <form method="GET" action="{{ url()->current() }}" class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
            <span>Show</span>
            <select name="per_page" onchange="this.form.submit()" class="h-9 rounded-lg border-gray-200 bg-white text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
            </select>
            <span>entries</span>
        </div>
        <div class="relative w-full md:max-w-xs">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">@svg('heroicon-o-magnifying-glass', 'h-4 w-4')</span>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search..." class="h-10 w-full rounded-lg border border-gray-200 bg-white pl-10 pr-3 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        </div>
    </form>

    <x-idcore::table>
        <thead class="bg-gray-50 dark:bg-gray-800/50">
            <tr>
                <th class="w-20 px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">No</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Nama Grup</th>
                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($groups as $group)
                <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/60">
                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $groups->firstItem() + $loop->index }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">
                                @svg('heroicon-o-shield-check', 'h-5 w-5')
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $group->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Guard: {{ $group->guard_name }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-1">
                            @can('group.edit')
                                <a href="{{ route('sistem.group.edit', $group->id) }}"
                                   class="inline-flex h-7 w-7 items-center justify-center rounded-full text-blue-600 transition hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-500/10">
                                    @svg('heroicon-o-pencil-square', 'h-3.5 w-3.5')
                                </a>
                            @endcan
                            @can('group.delete')
                                <button type="button" x-data
                                        @click.prevent="
                                            $confirm({
                                                title: 'Hapus Grup?',
                                                message: 'Grup {{ $group->name }} akan dihapus permanen.',
                                                confirmText: 'Ya, Hapus',
                                                variant: 'danger'
                                            }).then(ok => { if (ok) $el.nextElementSibling.submit(); });
                                        "
                                        class="inline-flex h-7 w-7 items-center justify-center rounded-full text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10">
                                    @svg('heroicon-o-trash', 'h-3.5 w-3.5')
                                </button>
                                <form action="{{ route('sistem.group.destroy', $group->id) }}" method="POST" class="hidden">
                                    @csrf @method('DELETE')
                                </form>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <x-idcore::table-empty colspan="3" message="Belum ada data grup." />
            @endforelse
        </tbody>
    </x-idcore::table>

    <x-idcore::pagination :paginator="$groups" />
</x-idcore::card>
@endsection

@extends('idcore::layouts.backend')
@section('title', 'Hak Akses')

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Hak Akses</h1>
        <x-idcore::breadcrumb :items="[['label' => 'Home', 'url' => route('dashboard')], ['label' => 'Hak Akses']]" />
    </div>
</div>

<x-idcore::card title="Hak Akses per Role" subtitle="Kelola permission per role dan akses edit untuk setiap role.">
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
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari role..." class="h-10 w-full rounded-lg border border-gray-200 bg-white pl-10 pr-3 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            </div>
        </form>

    <x-idcore::table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-800">
        <thead class="bg-gray-50 dark:bg-gray-800/50">
            <tr>
                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">No</th>
                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Role</th>
                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Jumlah Permission</th>
                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($roles as $role)
                <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/60">
                    <td class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">{{ $loop->iteration }}</td>
                    <td class="px-4 py-3 text-center font-medium text-gray-800 dark:text-white">{{ $role->name }}</td>
                    <td class="px-4 py-3 text-center">
                        <x-idcore::badge variant="indigo">{{ $role->permissions->count() }} permission</x-idcore::badge>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @can('hak-akses.edit')
                            <x-idcore::button size="xs" circle variant="warning" :href="route('sistem.hak-akses.edit', $role->id)">
                                @svg('heroicon-o-pencil-square', 'h-3.5 w-3.5')
                            </x-idcore::button>
                        @endcan
                    </td>
                </tr>
            @empty
                <x-idcore::table-empty colspan="4" message="Belum ada data role." />
            @endforelse
        </tbody>
    </x-idcore::table>

    @if(method_exists($roles, 'links'))
        <div class="mt-4">
            <x-idcore::pagination :paginator="$roles" />
        </div>
    @endif
</x-idcore::card>
@endsection

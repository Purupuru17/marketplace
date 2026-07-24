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
                <x-idcore::select name="per_page" :options="[10 => '10', 25 => '25', 50 => '50']" :selected="request('per_page', 10)" placeholder="" onchange="this.form.submit()" />
                <span>entries</span>
            </div>
            <div class="relative w-full md:max-w-xs">
                    <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400">@svg('heroicon-o-magnifying-glass', 'h-4 w-4')</span>
                    <x-idcore::input name="search" type="search" value="{{ request('search') }}" placeholder="Cari role..." />
            </div>
        </form>

    <x-idcore::table>
        <thead class="bg-gray-50 dark:bg-gray-800/50">
            <tr>
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">No</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Role</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Jumlah Permission</th>
                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($roles as $role)
                <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/60">
                    <td class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">{{ $roles->firstItem() + $loop->index }}</td>
                    <td class="px-6 py-4 font-medium text-gray-800 dark:text-white">{{ $role->name }}</td>
                    <td class="px-6 py-4">
                        <x-idcore::badge variant="indigo">{{ $role->permissions->count() }} permission</x-idcore::badge>
                    </td>
                    <td class="px-6 py-4 text-right">
                        @can('hak-akses.edit')
                            <x-idcore::button variant="outline-warning" size="xs" circle tooltip="Edit" :href="route('sistem.hak-akses.edit', $role->id)">
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

@extends('idcore::layouts.backend')
@section('title', 'Kelola User')

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Kelola User</h1>
        <x-idcore::breadcrumb :items="[['label' => 'Home', 'url' => route('dashboard')], ['label' => 'User']]" />
    </div>
    @can('user.create')
        <x-idcore::button variant="primary" :href="route('sistem.user.create')">@svg('heroicon-o-pencil-square', 'h-4 w-4') Tambah User</x-idcore::button>
    @endcan
</div>

<x-idcore::card title="Datatable User" subtitle="Daftar akun dan role yang tersedia" :padding="false">
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
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">User</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Email</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Role</th>
                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($users as $user)
                <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/60">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <x-idcore::avatar :name="$user->name" size="sm" />
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $user->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">ID: {{ $user->id }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $user->email }}</td>
                    <td class="px-6 py-4">
                        <div class="flex flex-wrap gap-1.5">
                            @forelse($user->roles as $role)
                                <x-idcore::badge variant="indigo">{{ $role->name }}</x-idcore::badge>
                            @empty
                                <x-idcore::badge>Tanpa role</x-idcore::badge>
                            @endforelse
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-1">
                            @can('user.edit')
                                <x-idcore::button variant="outline-warning" size="xs" circle tooltip="Edit" :href="route('sistem.user.edit', $user->id)">
                                    @svg('heroicon-o-pencil-square', 'h-4 w-4')
                                </x-idcore::button>
                            @endcan
                            @can('user.delete')
                                <x-idcore::button variant="outline-danger" size="xs" circle tooltip="Hapus"
                                    x-data
                                    @click.prevent="
                                        $confirm({
                                            title: 'Peringatan !',
                                            message: 'Apakah anda yakin akan menghapus data {{ $user->name }} ?',
                                            confirmText: 'Ya, Hapus',
                                            variant: 'danger'
                                        }).then(ok => { if (ok) $el.nextElementSibling.submit(); });
                                    ">
                                    @svg('heroicon-o-trash', 'h-4 w-4')
                                </x-idcore::button>
                                <form action="{{ route('sistem.user.destroy', $user->id) }}" method="POST" class="hidden">
                                    @csrf @method('DELETE')
                                </form>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <x-idcore::table-empty colspan="4" message="Belum ada data user." />
            @endforelse
        </tbody>
    </x-idcore::table>

    <x-idcore::pagination :paginator="$users" />
</x-idcore::card>
@endsection

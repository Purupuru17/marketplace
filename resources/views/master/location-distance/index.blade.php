@extends('idcore::layouts.backend')
@section('title', 'Jarak Antar Node')

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Jarak Antar Node</h1>
        <x-idcore::breadcrumb :items="[['label' => 'Home', 'url' => route('dashboard')], ['label' => 'Master Data'], ['label' => 'Jarak Antar Node']]" />
    </div>
    @can('location-distance.create')
        <x-idcore::button variant="primary" :href="route('master.location-distance.create')">Tambah Jarak</x-idcore::button>
    @endcan
</div>

<x-idcore::card title="Data Jarak Antar Node" subtitle="Grafik undirected: cukup satu baris per pasangan node" :padding="false">
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
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Rute</th>
                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Jarak (km)</th>
                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($locationDistances as $locationDistance)
                <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/60">
                    <td class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">{{ $locationDistances->firstItem() + $loop->index }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <span class="rounded-full bg-blue-50 px-3 py-1 text-sm font-medium text-blue-700 dark:bg-blue-500/10 dark:text-blue-300">{{ $locationDistance->origin->name }}</span>
                            @svg('heroicon-o-arrow-right', 'h-4 w-4 text-gray-400')
                            <span class="rounded-full bg-indigo-50 px-3 py-1 text-sm font-medium text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300">{{ $locationDistance->destination->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right font-semibold text-gray-900 dark:text-white">{{ $locationDistance->distance_km }} km</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-1">
                            @can('location-distance.edit')
                                <x-idcore::button variant="outline-warning" size="xs" circle tooltip="Edit" :href="route('master.location-distance.edit', $locationDistance->id)">
                                    @svg('heroicon-o-pencil-square', 'h-3.5 w-3.5')
                                </x-idcore::button>
                            @endcan
                            @can('location-distance.delete')
                                <x-idcore::button variant="outline-danger" size="xs" circle tooltip="Hapus"
                                    x-data
                                    @click.prevent="
                                        $confirm({
                                            title: 'Hapus Jarak?',
                                            message: 'Jarak {{ $locationDistance->origin->name }} - {{ $locationDistance->destination->name }} akan dihapus permanen.',
                                            confirmText: 'Ya, Hapus',
                                            variant: 'danger'
                                        }).then(ok => { if (ok) $el.nextElementSibling.submit(); });
                                    ">
                                    @svg('heroicon-o-trash', 'h-3.5 w-3.5')
                                </x-idcore::button>
                                <form action="{{ route('master.location-distance.destroy', $locationDistance->id) }}" method="POST" class="hidden">
                                    @csrf @method('DELETE')
                                </form>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <x-idcore::table-empty colspan="4" message="Belum ada data jarak antar node." />
            @endforelse
        </tbody>
    </x-idcore::table>

    <x-idcore::pagination :paginator="$locationDistances" />
</x-idcore::card>
@endsection

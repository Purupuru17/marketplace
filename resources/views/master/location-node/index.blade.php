@extends('idcore::layouts.backend')
@section('title', 'Node Lokasi')

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Node Lokasi</h1>
        <x-idcore::breadcrumb :items="[['label' => 'Home', 'url' => route('dashboard')], ['label' => 'Master Data'], ['label' => 'Node Lokasi']]" />
    </div>
    @can('location-node.create')
        <x-idcore::button variant="primary" :href="route('master.location-node.create')">Tambah Node Lokasi</x-idcore::button>
    @endcan
</div>

<x-idcore::card title="Data Node Lokasi" subtitle="Titik lokasi yang menjadi simpul perhitungan ongkir (Dijkstra)" :padding="false">
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
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Nama</th>
                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Latitude</th>
                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Longitude</th>
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($locationNodes as $locationNode)
                <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/60">
                    <td class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">{{ $locationNodes->firstItem() + $loop->index }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">
                                @svg('heroicon-o-map-pin', 'h-5 w-5')
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $locationNode->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ substr($locationNode->id, 0, 8) }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right text-gray-700 dark:text-gray-300">{{ $locationNode->lat ?? '-' }}</td>
                    <td class="px-6 py-4 text-right text-gray-700 dark:text-gray-300">{{ $locationNode->lng ?? '-' }}</td>
                    <td class="px-6 py-4 text-center">
                        @if($locationNode->status === 'active')
                            <x-idcore::badge variant="green">Active</x-idcore::badge>
                        @else
                            <x-idcore::badge variant="red">Inactive</x-idcore::badge>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-1">
                            @can('location-node.edit')
                                <x-idcore::button variant="outline-warning" size="xs" circle tooltip="Edit" :href="route('master.location-node.edit', $locationNode->id)">
                                    @svg('heroicon-o-pencil-square', 'h-3.5 w-3.5')
                                </x-idcore::button>
                            @endcan
                            @can('location-node.delete')
                                <x-idcore::button variant="outline-danger" size="xs" circle tooltip="Hapus"
                                    x-data
                                    @click.prevent="
                                        $confirm({
                                            title: 'Hapus Node Lokasi?',
                                            message: 'Node lokasi {{ $locationNode->name }} akan dihapus permanen.',
                                            confirmText: 'Ya, Hapus',
                                            variant: 'danger'
                                        }).then(ok => { if (ok) $el.nextElementSibling.submit(); });
                                    ">
                                    @svg('heroicon-o-trash', 'h-3.5 w-3.5')
                                </x-idcore::button>
                                <form action="{{ route('master.location-node.destroy', $locationNode->id) }}" method="POST" class="hidden">
                                    @csrf @method('DELETE')
                                </form>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <x-idcore::table-empty colspan="6" message="Belum ada data node lokasi." />
            @endforelse
        </tbody>
    </x-idcore::table>

    <x-idcore::pagination :paginator="$locationNodes" />
</x-idcore::card>
@endsection

@extends('idcore::layouts.backend')
@section('title', 'Store Level')

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Store Level</h1>
        <x-idcore::breadcrumb :items="[['label' => 'Home', 'url' => route('dashboard')], ['label' => 'Master Data'], ['label' => 'Store Level']]" />
    </div>
    @can('store-level.create')
        <x-idcore::button variant="primary" :href="route('master.store-level.create')">Tambah Store Level</x-idcore::button>
    @endcan
</div>

<x-idcore::card title="Data Store Level" subtitle="Paket level berlangganan untuk store" :padding="false">
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
                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Harga</th>
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Maks Produk</th>
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Maks Diskon</th>
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Campaign</th>
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($storeLevels as $storeLevel)
                <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/60">
                    <td class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">{{ $storeLevels->firstItem() + $loop->index }}</td>
                    <td class="px-6 py-4">
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $storeLevel->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Urutan: {{ $storeLevel->sort_order }}</p>
                    </td>
                    <td class="px-6 py-4 text-right text-gray-700 dark:text-gray-300">Rp {{ number_format($storeLevel->price, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 text-center text-gray-700 dark:text-gray-300">{{ $storeLevel->max_products ?? '-' }}</td>
                    <td class="px-6 py-4 text-center text-gray-700 dark:text-gray-300">{{ $storeLevel->max_discount ? $storeLevel->max_discount.'%' : '-' }}</td>
                    <td class="px-6 py-4 text-center">
                        @if($storeLevel->can_run_campaign)
                            <x-idcore::badge variant="green">Ya</x-idcore::badge>
                        @else
                            <x-idcore::badge variant="gray">Tidak</x-idcore::badge>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($storeLevel->status === 'active')
                            <x-idcore::badge variant="green">Active</x-idcore::badge>
                        @else
                            <x-idcore::badge variant="red">Inactive</x-idcore::badge>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-1">
                            @can('store-level.edit')
                                <x-idcore::button variant="outline-warning" size="xs" circle tooltip="Edit" :href="route('master.store-level.edit', $storeLevel->id)">
                                    @svg('heroicon-o-pencil-square', 'h-3.5 w-3.5')
                                </x-idcore::button>
                            @endcan
                            @can('store-level.delete')
                                <x-idcore::button variant="outline-danger" size="xs" circle tooltip="Hapus"
                                    x-data
                                    @click.prevent="
                                        $confirm({
                                            title: 'Hapus Store Level?',
                                            message: 'Store level {{ $storeLevel->name }} akan dihapus permanen.',
                                            confirmText: 'Ya, Hapus',
                                            variant: 'danger'
                                        }).then(ok => { if (ok) $el.nextElementSibling.submit(); });
                                    ">
                                    @svg('heroicon-o-trash', 'h-3.5 w-3.5')
                                </x-idcore::button>
                                <form action="{{ route('master.store-level.destroy', $storeLevel->id) }}" method="POST" class="hidden">
                                    @csrf @method('DELETE')
                                </form>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <x-idcore::table-empty colspan="8" message="Belum ada data store level." />
            @endforelse
        </tbody>
    </x-idcore::table>

    <x-idcore::pagination :paginator="$storeLevels" />
</x-idcore::card>
@endsection

@extends('idcore::layouts.backend')
@section('title', 'Varian Produk')

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Varian Produk</h1>
        <x-idcore::breadcrumb :items="[['label' => 'Home', 'url' => route('dashboard')], ['label' => 'Katalog'], ['label' => 'Varian Produk']]" />
    </div>
    @can('product-variant.create')
        <x-idcore::button variant="primary" :href="route('katalog.product-variant.create')">Tambah Varian</x-idcore::button>
    @endcan
</div>

<x-idcore::card title="Data Varian Produk" subtitle="SKU, harga, dan stok per toko" :padding="false">
    <form method="GET" action="{{ url()->current() }}" class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800 md:flex-row md:items-center md:justify-between">
        <div class="flex flex-wrap items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
            <span>Show</span>
            <x-idcore::select name="per_page" :options="[10 => '10', 25 => '25', 50 => '50']" :selected="request('per_page', 10)" placeholder="" onchange="this.form.submit()" />
            <span>entries</span>
            <x-idcore::select name="store_id" :options="$storeOptions" :selected="request('store_id')" placeholder="Semua Toko" onchange="this.form.submit()" />
            <x-idcore::select name="status" :options="['active' => 'Active', 'inactive' => 'Inactive']" :selected="request('status')" placeholder="Semua Status" onchange="this.form.submit()" />
        </div>
        <div class="relative w-full md:max-w-xs">
            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400">@svg('heroicon-o-magnifying-glass', 'h-4 w-4')</span>
            <x-idcore::input name="search" type="search" value="{{ request('search') }}" placeholder="Search SKU..." />
        </div>
    </form>

    <x-idcore::table>
        <thead class="bg-gray-50 dark:bg-gray-800/50">
            <tr>
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">No</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">SKU</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Produk</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Atribut</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Toko</th>
                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Harga</th>
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Stok</th>
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($variants as $variant)
                <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/60">
                    <td class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">{{ $variants->firstItem() + $loop->index }}</td>
                    <td class="px-6 py-4">
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $variant->sku }}</p>
                    </td>
                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $variant->product->name ?? '-' }}</td>
                    <td class="px-6 py-4">
                        @if($variant->attributeValues->isNotEmpty())
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $variant->attributeValues->sortBy(fn ($v) => $v->attribute?->name)->map->value->join(' · ') }}</span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $variant->store->store_name ?? '-' }}</td>
                    <td class="px-6 py-4 text-right font-medium text-gray-900 dark:text-white">Rp {{ number_format((float) $variant->price, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 text-center text-gray-700 dark:text-gray-300">{{ number_format($variant->stock) }} <span class="text-xs text-gray-400">· {{ number_format($variant->weight_grams) }}g</span></td>
                    <td class="px-6 py-4 text-center">
                        @if($variant->status === 'active')
                            <x-idcore::badge variant="green">Active</x-idcore::badge>
                        @else
                            <x-idcore::badge variant="red">Inactive</x-idcore::badge>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-1">
                            @can('product-variant.edit')
                                <x-idcore::button variant="outline-warning" size="xs" circle tooltip="Edit" :href="route('katalog.product-variant.edit', $variant->id)">
                                    @svg('heroicon-o-pencil-square', 'h-3.5 w-3.5')
                                </x-idcore::button>
                            @endcan
                            @can('product-variant.delete')
                                <x-idcore::button variant="outline-danger" size="xs" circle tooltip="Hapus"
                                    x-data
                                    @click.prevent="
                                        $confirm({
                                            title: 'Hapus Varian?',
                                            message: 'Varian {{ $variant->sku }} akan dihapus.',
                                            confirmText: 'Ya, Hapus',
                                            variant: 'danger'
                                        }).then(ok => { if (ok) $el.nextElementSibling.submit(); });
                                    ">
                                    @svg('heroicon-o-trash', 'h-3.5 w-3.5')
                                </x-idcore::button>
                                <form action="{{ route('katalog.product-variant.destroy', $variant->id) }}" method="POST" class="hidden">
                                    @csrf @method('DELETE')
                                </form>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <x-idcore::table-empty colspan="9" message="Belum ada data varian produk." />
            @endforelse
        </tbody>
    </x-idcore::table>

    <x-idcore::pagination :paginator="$variants" />
</x-idcore::card>
@endsection

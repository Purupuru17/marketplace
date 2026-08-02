@extends('idcore::layouts.backend')
@section('title', 'Subscription')

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Subscription</h1>
        <x-idcore::breadcrumb :items="[['label' => 'Home', 'url' => route('dashboard')], ['label' => 'Toko'], ['label' => 'Subscription']]" />
    </div>
    @can('subscription.create')
        <x-idcore::button variant="primary" :href="route('toko.subscription.create')">Tambah Subscription</x-idcore::button>
    @endcan
</div>

<x-idcore::card title="Data Subscription" subtitle="Langganan level toko (invoice dibuat otomatis)" :padding="false">
    <form method="GET" action="{{ url()->current() }}" class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800 md:flex-row md:items-center md:justify-between">
        <div class="flex flex-wrap items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
            <span>Show</span>
            <x-idcore::select name="per_page" :options="[10 => '10', 25 => '25', 50 => '50']" :selected="request('per_page', 10)" placeholder="" onchange="this.form.submit()" />
            <span>entries</span>
            <x-idcore::select name="status" :options="['active' => 'Active', 'expired' => 'Expired', 'cancelled' => 'Cancelled']" :selected="request('status')" placeholder="Semua Status" onchange="this.form.submit()" />
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
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Toko</th>
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Level</th>
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Periode</th>
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Auto Renew</th>
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($subscriptions as $subscription)
                <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/60">
                    <td class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">{{ $subscriptions->firstItem() + $loop->index }}</td>
                    <td class="px-6 py-4">
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $subscription->store->store_name ?? '-' }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $subscription->store->store_code ?? '' }}</p>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($subscription->storeLevel)
                            <x-idcore::badge variant="indigo">{{ $subscription->storeLevel->name }}</x-idcore::badge>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center text-gray-700 dark:text-gray-300">{{ $subscription->starts_at->format('d M Y') }} - {{ $subscription->ends_at->format('d M Y') }}</td>
                    <td class="px-6 py-4 text-center">
                        @if($subscription->auto_renew)
                            <x-idcore::badge variant="green">Ya</x-idcore::badge>
                        @else
                            <x-idcore::badge variant="gray">Tidak</x-idcore::badge>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($subscription->status === 'active')
                            <x-idcore::badge variant="green">Active</x-idcore::badge>
                        @elseif($subscription->status === 'expired')
                            <x-idcore::badge variant="yellow">Expired</x-idcore::badge>
                        @else
                            <x-idcore::badge variant="red">Cancelled</x-idcore::badge>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-1">
                            @can('subscription.edit')
                                <x-idcore::button variant="outline-warning" size="xs" circle tooltip="Edit" :href="route('toko.subscription.edit', $subscription->id)">
                                    @svg('heroicon-o-pencil-square', 'h-3.5 w-3.5')
                                </x-idcore::button>
                            @endcan
                            @can('subscription.delete')
                                <x-idcore::button variant="outline-danger" size="xs" circle tooltip="Hapus"
                                    x-data
                                    @click.prevent="
                                        $confirm({
                                            title: 'Hapus Subscription?',
                                            message: 'Subscription {{ $subscription->store->store_name ?? '' }} akan dihapus permanen.',
                                            confirmText: 'Ya, Hapus',
                                            variant: 'danger'
                                        }).then(ok => { if (ok) $el.nextElementSibling.submit(); });
                                    ">
                                    @svg('heroicon-o-trash', 'h-3.5 w-3.5')
                                </x-idcore::button>
                                <form action="{{ route('toko.subscription.destroy', $subscription->id) }}" method="POST" class="hidden">
                                    @csrf @method('DELETE')
                                </form>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <x-idcore::table-empty colspan="7" message="Belum ada data subscription." />
            @endforelse
        </tbody>
    </x-idcore::table>

    <x-idcore::pagination :paginator="$subscriptions" />
</x-idcore::card>
@endsection

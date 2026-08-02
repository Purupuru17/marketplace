@extends('idcore::layouts.backend')
@section('title', 'Invoice Subscription')

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Invoice Subscription</h1>
        <x-idcore::breadcrumb :items="[['label' => 'Home', 'url' => route('dashboard')], ['label' => 'Toko'], ['label' => 'Invoice Subscription']]" />
    </div>
    @can('subscription-invoice.create')
        <x-idcore::button variant="primary" :href="route('toko.subscription-invoice.create')">Tambah Invoice</x-idcore::button>
    @endcan
</div>

<x-idcore::card title="Data Invoice Subscription" subtitle="Tagihan langganan toko" :padding="false">
    <form method="GET" action="{{ url()->current() }}" class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800 md:flex-row md:items-center md:justify-between">
        <div class="flex flex-wrap items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
            <span>Show</span>
            <x-idcore::select name="per_page" :options="[10 => '10', 25 => '25', 50 => '50']" :selected="request('per_page', 10)" placeholder="" onchange="this.form.submit()" />
            <span>entries</span>
            <x-idcore::select name="status" :options="['pending' => 'Pending', 'paid' => 'Paid', 'overdue' => 'Overdue']" :selected="request('status')" placeholder="Semua Status" onchange="this.form.submit()" />
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
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">No Invoice</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Toko</th>
                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Jumlah</th>
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Jatuh Tempo</th>
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($invoices as $invoice)
                <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/60">
                    <td class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">{{ $invoices->firstItem() + $loop->index }}</td>
                    <td class="px-6 py-4">
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $invoice->invoice_no }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $invoice->subscription->storeLevel->name ?? '-' }}</p>
                    </td>
                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $invoice->subscription->store->store_name ?? '-' }}</td>
                    <td class="px-6 py-4 text-right font-semibold text-gray-900 dark:text-white">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 text-center text-gray-700 dark:text-gray-300">{{ $invoice->due_at->format('d M Y') }}</td>
                    <td class="px-6 py-4 text-center">
                        @if($invoice->status === 'paid')
                            <x-idcore::badge variant="green">Paid</x-idcore::badge>
                        @elseif($invoice->status === 'overdue')
                            <x-idcore::badge variant="red">Overdue</x-idcore::badge>
                        @else
                            <x-idcore::badge variant="yellow">Pending</x-idcore::badge>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-1">
                            @can('subscription-invoice.edit')
                                <x-idcore::button variant="outline-warning" size="xs" circle tooltip="Edit" :href="route('toko.subscription-invoice.edit', $invoice->id)">
                                    @svg('heroicon-o-pencil-square', 'h-3.5 w-3.5')
                                </x-idcore::button>
                            @endcan
                            @can('subscription-invoice.delete')
                                <x-idcore::button variant="outline-danger" size="xs" circle tooltip="Hapus"
                                    x-data
                                    @click.prevent="
                                        $confirm({
                                            title: 'Hapus Invoice?',
                                            message: 'Invoice {{ $invoice->invoice_no }} akan dihapus permanen.',
                                            confirmText: 'Ya, Hapus',
                                            variant: 'danger'
                                        }).then(ok => { if (ok) $el.nextElementSibling.submit(); });
                                    ">
                                    @svg('heroicon-o-trash', 'h-3.5 w-3.5')
                                </x-idcore::button>
                                <form action="{{ route('toko.subscription-invoice.destroy', $invoice->id) }}" method="POST" class="hidden">
                                    @csrf @method('DELETE')
                                </form>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <x-idcore::table-empty colspan="7" message="Belum ada data invoice subscription." />
            @endforelse
        </tbody>
    </x-idcore::table>

    <x-idcore::pagination :paginator="$invoices" />
</x-idcore::card>
@endsection

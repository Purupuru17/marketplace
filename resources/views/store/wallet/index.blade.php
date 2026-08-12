@extends('idcore::layouts.backend')
@section('title', 'Saldo Toko')

@section('content')
@php
    $txnStyles = [
        'hold' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
        'release' => 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400',
        'credit' => 'bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400',
        'debit' => 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400',
    ];
    $txnLabels = [
        'hold' => 'Dana Ditahan',
        'release' => 'Dana Dilepas',
        'credit' => 'Kredit',
        'debit' => 'Debit',
    ];
    $wdStyles = [
        'pending' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
        'approved' => 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400',
        'rejected' => 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400',
        'completed' => 'bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400',
    ];
@endphp

<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $title }}</h1>
        <x-idcore::breadcrumb :items="$breadcrumb" />
    </div>
</div>

@if($wallets->isEmpty())
    <x-idcore::card>
        <div class="py-10 text-center text-gray-500 dark:text-gray-400">
            Belum ada toko. Buat toko terlebih dahulu untuk mengelola saldo.
        </div>
    </x-idcore::card>
@else
    <div class="space-y-6">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach($wallets as $wallet)
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">{{ $wallet->store->store_name }}</p>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Saldo Tersedia</dt>
                            <dd class="font-bold text-green-600 dark:text-green-400">Rp {{ number_format((float) $wallet->available_balance, 0, ',', '.') }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Saldo Mengendap</dt>
                            <dd class="font-semibold text-amber-600 dark:text-amber-400">Rp {{ number_format((float) $wallet->held_balance, 0, ',', '.') }}</dd>
                        </div>
                        <div class="flex items-center justify-between border-t border-gray-100 pt-3 dark:border-gray-800">
                            <dt class="text-gray-500 dark:text-gray-400">Dapat Ditarik</dt>
                            <dd class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format((float) $withdrawables[$wallet->id] ?? 0, 0, ',', '.') }}</dd>
                        </div>
                    </dl>
                </div>
            @endforeach
        </div>

        @can($rolesName.'.create')
            <x-idcore::card title="Ajukan Penarikan" subtitle="Dana masuk ke rekening yang didaftarkan setelah disetujui admin">
                @if($errors->any())
                    <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-600 dark:bg-red-500/10 dark:text-red-400">
                        {{ $errors->first() }}
                    </div>
                @endif
                <form method="POST" action="{{ route('toko.wallet.store') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        @if($wallets->count() > 1)
                            <div>
                                <x-idcore::select name="store_id" label="Toko" :options="$wallets->pluck('store.store_name', 'store_id')->all()" :selected="old('store_id')" />
                            </div>
                        @endif
                        <div>
                            <x-idcore::input name="amount" label="Jumlah (Rp)" type="number" step="0.01" min="0" value="{{ old('amount') }}" placeholder="Contoh: 50000" />
                        </div>
                        <div>
                            <x-idcore::input name="bank_name" label="Nama Bank" type="text" value="{{ old('bank_name') }}" placeholder="Contoh: Bank Simulasi" />
                        </div>
                        <div>
                            <x-idcore::input name="account_number" label="No. Rekening" type="text" value="{{ old('account_number') }}" placeholder="Nomor rekening tujuan" />
                        </div>
                        <div>
                            <x-idcore::input name="account_name" label="Nama Pemilik Rekening" type="text" value="{{ old('account_name') }}" placeholder="Sesuai nama pemilik rekening" />
                        </div>
                    </div>
                    <div>
                        <x-idcore::button variant="primary" type="submit">Kirim Permintaan</x-idcore::button>
                    </div>
                </form>
            </x-idcore::card>
        @endcan

        <x-idcore::card title="Transaksi Terbaru" :padding="false">
            <x-idcore::table>
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Toko</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tipe</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Keterangan</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Jumlah</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($transactions as $transaction)
                        <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/60">
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $transaction->wallet?->store?->store_name ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $txnStyles[$transaction->type] ?? 'bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-300' }}">
                                    {{ $txnLabels[$transaction->type] ?? ucfirst($transaction->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $transaction->notes }}</td>
                            <td class="px-6 py-4 text-right font-semibold text-gray-900 dark:text-white">Rp {{ number_format((float) $transaction->amount, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">{{ $transaction->created_at->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <x-idcore::table-empty colspan="5" message="Belum ada transaksi." />
                    @endforelse
                </tbody>
            </x-idcore::table>
        </x-idcore::card>

        <x-idcore::card title="Permintaan Penarikan" :padding="false">
            <x-idcore::table>
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Toko</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Rekening</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Jumlah</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tanggal</th>
                        @if($isAdmin)
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($withdrawals as $withdrawal)
                        <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/60">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $withdrawal->wallet?->store?->store_name ?? '-' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $withdrawal->bank_name }} · {{ $withdrawal->account_number }}</p>
                            </td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $withdrawal->account_name }}</td>
                            <td class="px-6 py-4 text-right font-semibold text-gray-900 dark:text-white">Rp {{ number_format((float) $withdrawal->amount, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $wdStyles[$withdrawal->status] ?? 'bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-300' }}">
                                    {{ ucfirst($withdrawal->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">{{ $withdrawal->created_at->format('d M Y H:i') }}</td>
                            @if($isAdmin)
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if($withdrawal->status === 'pending')
                                            <form method="POST" action="{{ route('toko.wallet.process', [$withdrawal->id, 'approve']) }}">
                                                @csrf
                                                <x-idcore::button variant="success" size="xs" type="submit">Approve</x-idcore::button>
                                            </form>
                                            <form method="POST" action="{{ route('toko.wallet.process', [$withdrawal->id, 'reject']) }}">
                                                @csrf
                                                <x-idcore::button variant="danger" size="xs" type="submit">Reject</x-idcore::button>
                                            </form>
                                        @elseif($withdrawal->status === 'approved')
                                            <form method="POST" action="{{ route('toko.wallet.process', [$withdrawal->id, 'complete']) }}">
                                                @csrf
                                                <x-idcore::button variant="outline-primary" size="xs" type="submit">Tandai Transfer</x-idcore::button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <x-idcore::table-empty colspan="{{ $isAdmin ? 6 : 5 }}" message="Belum ada permintaan penarikan." />
                    @endforelse
                </tbody>
            </x-idcore::table>
        </x-idcore::card>
    </div>
@endif
@endsection

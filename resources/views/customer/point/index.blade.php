@extends('customer.layouts.app')
@section('title', 'Poin Saya')

@section('content')
<header class="sticky top-0 z-30 bg-white border-b border-gray-100 px-4 py-3">
    <h1 class="font-bold text-[15px] text-gray-900">Poin Saya</h1>
</header>

<main class="px-4">
    <section class="mt-4 rounded-2xl bg-gradient-to-br from-emerald-700 to-emerald-800 p-4 text-white">
        <p class="text-xs font-medium text-emerald-100">Saldo Poin</p>
        <p class="mt-1 text-3xl font-extrabold">{{ number_format($availablePoints) }}</p>
        <p class="mt-2 text-[11px] text-emerald-100">1 poin per Rp 5.000 belanja · 100 poin = Rp 1.000</p>
    </section>

    <section class="mt-4 bg-white rounded-xl border border-gray-100 overflow-hidden">
        <div class="border-b border-gray-100 px-3.5 py-3">
            <h2 class="text-[13px] font-semibold text-gray-900">Riwayat Poin</h2>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($transactions as $tx)
                <div class="flex items-center justify-between gap-3 px-3.5 py-3">
                    <div class="min-w-0">
                        <p class="text-[13px] font-medium text-gray-900">{{ $tx->description ?? ucfirst($tx->type) }}</p>
                        <p class="text-[11px] text-gray-500">{{ $tx->created_at->format('d M Y H:i') }}</p>
                    </div>
                    <span class="shrink-0 text-sm font-bold {{ $tx->points > 0 ? 'text-emerald-600' : 'text-red-500' }}">
                        {{ $tx->points > 0 ? '+' : '' }}{{ number_format($tx->points) }}
                    </span>
                </div>
            @empty
                <div class="px-3.5 py-12 text-center text-sm text-gray-500">Belum ada riwayat poin.</div>
            @endforelse
        </div>
        <div class="border-t border-gray-100 px-3.5 py-3">
            {{ $transactions->links() }}
        </div>
    </section>
</main>
@endsection
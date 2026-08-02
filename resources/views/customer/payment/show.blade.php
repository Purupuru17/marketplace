@extends('customer.layouts.app')
@section('title', 'Pembayaran')

@section('content')
<div class="mx-auto max-w-2xl">
    <a href="{{ route('customer.order.show', $invoice) }}"
       class="mb-6 inline-flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
        @svg('heroicon-o-arrow-left', 'h-4 w-4') Kembali ke Pesanan
    </a>

    @if($payment->status === 'paid')
        <div class="rounded-2xl border border-green-200 bg-green-50 p-8 text-center dark:border-green-500/30 dark:bg-green-500/10">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-green-100 text-green-600 dark:bg-green-500/20 dark:text-green-400">
                @svg('heroicon-o-check', 'h-7 w-7')
            </div>
            <h1 class="mt-4 text-xl font-bold text-gray-900 dark:text-white">Pembayaran Lunas</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $invoice->invoice_no }} · Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}
                · {{ $payment->paid_at?->format('d M Y H:i') }}
            </p>
        </div>
    @elseif($expired)
        <div class="rounded-2xl border border-red-200 bg-red-50 p-8 text-center dark:border-red-500/30 dark:bg-red-500/10">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-500/20 dark:text-red-400">
                @svg('heroicon-o-clock', 'h-7 w-7')
            </div>
            <h1 class="mt-4 text-xl font-bold text-gray-900 dark:text-white">Pembayaran Kedaluwarsa</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Batas waktu pembayaran sudah lewat. Buat pembayaran baru untuk melanjutkan.</p>
            <form method="POST" action="{{ route('customer.payment.store', $invoice) }}" class="mt-6">
                @csrf
                <button type="submit"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                    Bayar Ulang
                </button>
            </form>
        </div>
    @else
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-6 py-5 dark:border-gray-800">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Pembayaran {{ $invoice->invoice_no }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Selesaikan sebelum <span class="font-semibold text-gray-900 dark:text-white">{{ $payment->expired_at->format('d M Y H:i') }}</span>
                </p>
            </div>

            <div class="space-y-4 px-6 py-6">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Dibayar</p>
                    <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</p>
                </div>

                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-800/50">
                    @if($payment->payment_method === 'e_wallet')
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Aplikasi</p>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $info['app'] }}</p>
                        </div>
                        <div class="mt-2 flex items-center justify-between">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Nomor Tujuan</p>
                            <p class="font-mono font-semibold text-gray-900 dark:text-white">{{ $info['account'] }}</p>
                        </div>
                    @else
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Bank</p>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $info['bank'] }}</p>
                        </div>
                        <div class="mt-2 flex items-center justify-between">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Virtual Account</p>
                            <p class="font-mono font-semibold text-gray-900 dark:text-white">{{ $info['virtual_account'] }}</p>
                        </div>
                    @endif
                    <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">{{ $info['instruction'] }}</p>
                </div>
            </div>

            <div class="border-t border-gray-100 px-6 py-5 dark:border-gray-800">
                <form method="POST" action="{{ route('customer.payment.store', $invoice) }}">
                    @csrf
                    <button type="submit"
                            class="w-full rounded-lg bg-indigo-600 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Saya Sudah Membayar
                    </button>
                </form>
                @if($errors->any())
                    <p class="mt-2 text-center text-sm text-red-600 dark:text-red-400">{{ $errors->first() }}</p>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection

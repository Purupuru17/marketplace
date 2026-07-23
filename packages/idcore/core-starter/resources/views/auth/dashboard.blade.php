@extends('idcore::layouts.backend')
@section('title', 'Beranda')

@section('content')
@php
    $user = auth()->user();
    $activeRole = \IdCore\CoreStarter\Support\ActiveRole::get($user);
    $stats = [
        ['label' => 'Users', 'value' => '3,782', 'icon' => 'users', 'change' => '11.01%', 'tone' => 'success'],
        ['label' => 'Roles', 'value' => '5,359', 'icon' => 'shield-check', 'change' => '9.05%', 'tone' => 'danger'],
    ];
    $bars = [42, 96, 50, 74, 46, 49, 72, 26, 53, 97, 70, 27];
    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
@endphp

<div class="space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Dashboard</p>
            <h1 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">Selamat datang, {{ $user?->name ?? 'Admin' }}</h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Role aktif: <span class="font-semibold text-brand-600 dark:text-brand-300">{{ $activeRole?->name ?? 'Belum ada role' }}</span></p>
        </div>
        <div class="flex items-center gap-2">
            <x-idcore::button variant="light" size="sm">
                @svg('heroicon-o-calendar', 'h-4 w-4') Jul 5 - Jul 11
            </x-idcore::button>
            <x-idcore::button variant="primary" size="sm">
                @svg('heroicon-o-arrow-down-tray', 'h-4 w-4') Export
            </x-idcore::button>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(360px,0.7fr)]">
        <div class="space-y-6">
            <div class="grid gap-6 md:grid-cols-2">
                @foreach($stats as $stat)
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-start justify-between">
                            <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                @svg('heroicon-o-' . $stat['icon'], 'h-6 w-6')
                            </div>
                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold {{ $stat['tone'] === 'success' ? 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-500' : 'bg-danger-50 text-danger-600 dark:bg-danger-500/10 dark:text-danger-500' }}">
                                @if($stat['tone'] === 'success')
                                    @svg('heroicon-o-arrow-trending-up', 'h-3.5 w-3.5')
                                @else
                                    @svg('heroicon-o-arrow-trending-down', 'h-3.5 w-3.5')
                                @endif
                                {{ $stat['change'] }}
                            </span>
                        </div>
                        <div class="mt-7">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $stat['label'] }}</p>
                            <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $stat['value'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Monthly Sales</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ringkasan performa bulanan</p>
                    </div>
                    <x-idcore::button size="sm" variant="ghost" class="!h-9 !w-9 !p-0">
                        @svg('heroicon-o-ellipsis-vertical', 'h-5 w-5')
                    </x-idcore::button>
                </div>

                <div class="flex h-64 items-end gap-3 border-b border-gray-100 px-2 pb-8 dark:border-gray-800 sm:gap-5">
                    @foreach($bars as $index => $height)
                        <div class="flex flex-1 flex-col items-center justify-end gap-3">
                            <div class="w-full max-w-8 rounded-t-md bg-brand-600 transition hover:bg-brand-700" style="height: {{ $height }}%;"></div>
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $months[$index] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="p-6 text-center">
                <h2 class="text-left text-lg font-bold text-gray-900 dark:text-white">Monthly Target</h2>
                <p class="mt-1 text-left text-sm text-gray-500 dark:text-gray-400">Target yang ditetapkan bulan ini</p>

                <div class="relative mx-auto mt-8 flex h-56 w-56 items-center justify-center rounded-full" style="background: conic-gradient(#465fff 0 272deg, #eef2ff 272deg 360deg);">
                    <div class="flex h-40 w-40 flex-col items-center justify-center rounded-full bg-white dark:bg-gray-900">
                        <span class="text-4xl font-bold text-gray-900 dark:text-white">75.55%</span>
                        <span class="mt-3 inline-flex items-center gap-1 rounded-full bg-success-50 px-2.5 py-1 text-xs font-semibold text-success-600 dark:bg-success-500/10 dark:text-success-500">
                            @svg('heroicon-o-arrow-trending-up', 'h-3.5 w-3.5') 10%
                        </span>
                    </div>
                </div>

                <p class="mx-auto mt-4 max-w-sm text-sm leading-6 text-gray-500 dark:text-gray-400">Anda menghasilkan $3287 hari ini, lebih tinggi dari bulan lalu.</p>
            </div>

            <div class="grid grid-cols-3 border-t border-gray-100 bg-gray-50/70 dark:border-gray-800 dark:bg-gray-950/50">
                @foreach([['Target', '$20K', 'down'], ['Revenue', '$20K', 'up'], ['Today', '$20K', 'up']] as $item)
                    <div class="px-4 py-5 text-center">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $item[0] }}</p>
                        <p class="mt-2 text-xl font-bold text-gray-900 dark:text-white">
                            {{ $item[1] }}
                            @if($item[2] === 'up')
                                @svg('heroicon-o-arrow-trending-up', 'h-4 w-4 text-success-600 inline')
                            @else
                                @svg('heroicon-o-arrow-trending-down', 'h-4 w-4 text-danger-600 inline')
                            @endif
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Statistics</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Target yang ditetapkan untuk setiap bulan</p>
                </div>
                <div class="inline-flex rounded-lg bg-gray-100 p-1 dark:bg-gray-800">
                    <button class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm dark:bg-gray-900 dark:text-white">Overview</button>
                    <button class="px-4 py-2 text-sm font-semibold text-gray-500">Sales</button>
                    <button class="px-4 py-2 text-sm font-semibold text-gray-500">Revenue</button>
                </div>
            </div>
            <div class="mt-8 h-56 rounded-xl border border-dashed border-gray-200 bg-gradient-to-b from-brand-50 to-white p-5 dark:border-gray-800 dark:from-brand-900/20 dark:to-gray-900">
                <div class="flex h-full items-end gap-2">
                    @foreach([32, 44, 38, 58, 50, 63, 59, 72, 67, 82, 79, 76] as $height)
                        <div class="flex-1 rounded-t-md bg-brand-500/25" style="height: {{ $height }}%;"></div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Aktivitas Terbaru</h2>
            <div class="mt-6 space-y-5">
                @php $activities = [['heroicon-o-user-plus', 'User baru ditambahkan', '2 menit lalu'], ['heroicon-o-shield-check', 'Permission role diperbarui', '18 menit lalu'], ['heroicon-o-list-bullet', 'Menu admin disusun ulang', '1 jam lalu']]; @endphp
                @foreach($activities as $activity)
                    <div class="flex gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            @svg($activity[0], 'h-5 w-5')
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $activity[1] }}</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $activity[2] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

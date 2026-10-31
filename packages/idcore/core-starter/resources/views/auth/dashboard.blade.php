@extends('idcore::layouts.backend')
@section('title', 'Beranda')

@section('content')

<div class="space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Dashboard</p>
            <h1 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">Selamat datang, {{ $user?->name ?? '-' }}</h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Role : <span class="font-semibold text-brand-600 dark:text-brand-400">{{ $activeRole?->name ?? 'Belum ada role' }}</span></p>
        </div>
    </div>
</div>


@endsection

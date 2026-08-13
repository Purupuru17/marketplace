@props([
    'title' => 'Masuk',
    'subtitle' => null,
    'pageTitle' => null,
    'brandText' => null,
    'brandTagline' => null,
])

@php
    $pageTitle = $pageTitle ?: $title . ' - ' . config('app.name');
    $brandText = $brandText ?: config('app.name');
    $brandTagline = $brandTagline ?: 'RBAC, menu dinamis, dan komponen admin reusable dalam satu package Laravel.';
@endphp

<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-50 antialiased dark:bg-gray-950" x-data x-init="$store.theme.init()">

    <div class="relative flex h-screen flex-row justify-center bg-gray-50 dark:bg-gray-950">
        <!-- Konten -->
        <div class="relative z-10 flex w-full flex-col bg-white/95 p-6 dark:bg-gray-950 sm:p-10 lg:w-1/2 lg:justify-center lg:bg-transparent lg:p-16">
            <div class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center">
                {{ $slot }}
            </div>
        </div>

        <!-- Brand panel -->
        <div class="relative hidden h-full w-full items-center bg-brand-950 dark:bg-white/5 lg:grid lg:w-1/2">
            <div class="flex items-center justify-center">
                <div class="flex flex-col items-center max-w-xs">
                    <a href="{{ route('dashboard') }}" class="mb-4 block">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 text-2xl font-bold text-white">
                            {{ substr($brandText, 0, 1) }}
                        </div>
                    </a>
                    <p class="text-center text-base font-semibold text-white">{{ $brandText }}</p>
                    <p class="mt-2 text-center text-sm text-gray-400 dark:text-white/60">{{ $brandTagline }}</p>
                </div>
            </div>
        </div>

        <!-- Dark toggler -->
        <div class="fixed bottom-6 right-6 z-50 hidden sm:block">
            <button type="button" @click="$store.theme.toggle()"
                    class="inline-flex size-14 items-center justify-center rounded-full bg-brand-500 text-white shadow-theme-lg transition-colors hover:bg-brand-600">
                <span x-show="!$store.theme.dark">@svg('heroicon-o-moon', 'h-5 w-5')</span>
                <span x-show="$store.theme.dark" x-cloak>@svg('heroicon-o-sun', 'h-5 w-5')</span>
            </button>
        </div>
    </div>
</body>
</html>
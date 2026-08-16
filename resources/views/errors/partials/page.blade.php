<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $code }} - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data x-init="$store.theme.init()">
    <div class="relative flex h-screen w-full flex-col items-center justify-center bg-gray-50 px-4 dark:bg-gray-950">
        <div class="w-full max-w-md">
            <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-6xl font-bold text-brand-500 dark:text-brand-400">{{ $code }}</p>
                <h1 class="mt-4 text-xl font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h1>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $message }}</p>
                <a href="{{ url('/') }}" class="mt-6 inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                    @svg('heroicon-o-arrow-left', 'h-4 w-4') Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</body>
</html>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Panel') - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased bg-gray-50 text-gray-800 dark:bg-gray-950 dark:text-gray-200"
      x-data x-init="$store.theme.init()">

    <div class="min-h-screen flex bg-gray-50 dark:bg-gray-950">
        @include('idcore::layouts.partials.sidebar')

        <div class="flex-1 flex min-w-0 flex-col">
            @include('idcore::layouts.partials.header')

            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                @if(session('success'))
                    <div x-init="$store.toast.success(@js(session('success')))"></div>
                @endif
                @if(session('error'))
                    <div x-init="$store.toast.error(@js(session('error')))"></div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    <x-idcore::toast />

    @stack('scripts')
</body>
</html>

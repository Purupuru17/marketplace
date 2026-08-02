<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased bg-gray-50 text-gray-800 dark:bg-gray-950 dark:text-gray-200"
      x-data x-init="$store.theme.init()">

    <header class="sticky top-0 z-40 border-b border-gray-200 bg-white/95 backdrop-blur dark:border-gray-800 dark:bg-gray-900/95">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="{{ route('storefront.index') }}" class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                {{ config('app.name') }}
            </a>

            <nav class="flex items-center gap-1 text-sm sm:gap-4">
                @auth('customer')
                    @php
                        $cartCount = app(\App\Services\Customer\CartService::class)->count(auth('customer')->user());
                    @endphp
                    <a href="{{ route('customer.cart.index') }}"
                       class="inline-flex items-center gap-1 rounded-lg px-3 py-2 font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white">
                        @svg('heroicon-o-shopping-cart', 'h-4 w-4')
                        Keranjang
                        @if($cartCount > 0)
                            <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-indigo-600 px-1.5 text-xs font-semibold text-white">{{ $cartCount }}</span>
                        @endif
                    </a>
                    <span class="hidden text-gray-500 dark:text-gray-400 sm:inline">{{ auth('customer')->user()->name }}</span>
                    <form method="POST" action="{{ route('customer.auth.logout') }}">
                        @csrf
                        <button type="submit"
                                class="rounded-lg px-3 py-2 font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white">
                            Keluar
                        </button>
                    </form>
                @else
                    <a href="{{ route('customer.auth.login') }}"
                       class="rounded-lg px-3 py-2 font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white">
                        Masuk
                    </a>
                    <a href="{{ route('customer.auth.register') }}"
                       class="rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-500">
                        Daftar
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if(session('success'))
            <div x-init="$store.toast.success(@js(session('success')))"></div>
        @endif
        @if(session('error'))
            <div x-init="$store.toast.error(@js(session('error')))"></div>
        @endif
        @yield('content')
    </main>

    <footer class="border-t border-gray-200 py-6 text-center text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
        &copy; {{ date('Y') }} {{ config('app.name') }}. Hak cipta dilindungi.
    </footer>

    <x-idcore::toast />

    @stack('scripts')
</body>
</html>

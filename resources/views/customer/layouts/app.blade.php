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
                    <span class="hidden text-gray-500 dark:text-gray-400 md:inline">{{ auth('customer')->user()->name }}</span>
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

        <nav class="border-t border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-1 px-4 sm:px-6 lg:px-8">
                @php
                    $cartCount = auth('customer')->check()
                        ? app(\App\Services\Customer\CartService::class)->count(auth('customer')->user())
                        : 0;
                    $menu = [
                        ['label' => 'Beranda', 'icon' => 'heroicon-o-home', 'route' => 'storefront.index', 'pattern' => 'storefront.*', 'auth' => false],
                        ['label' => 'Keranjang', 'icon' => 'heroicon-o-shopping-cart', 'route' => 'customer.cart.index', 'pattern' => 'customer.cart.*', 'auth' => true, 'count' => $cartCount ?? 0],
                        ['label' => 'Pesanan', 'icon' => 'heroicon-o-receipt-refund', 'route' => 'customer.order.index', 'pattern' => 'customer.order.*', 'auth' => true],
                        ['label' => 'Poin', 'icon' => 'heroicon-o-star', 'route' => 'customer.point.index', 'pattern' => 'customer.point.*', 'auth' => true],
                        ['label' => 'Favorit', 'icon' => 'heroicon-o-heart', 'route' => 'customer.favorite.index', 'pattern' => 'customer.favorite.*', 'auth' => true],
                        ['label' => 'Alamat Saya', 'icon' => 'heroicon-o-map-pin', 'route' => 'customer.address.index', 'pattern' => 'customer.address.*', 'auth' => true],
                    ];
                @endphp
                @foreach($menu as $item)
                    @if($item['auth'] && ! auth('customer')->check())
                        @continue
                    @endif
                    <a href="{{ route($item['route']) }}"
                       class="inline-flex items-center gap-1.5 border-b-2 px-3 py-3 text-sm font-medium transition-colors
                              {{ request()->routeIs($item['pattern'])
                                    ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400'
                                    : 'border-transparent text-gray-600 hover:border-gray-300 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white' }}">
                        @svg($item['icon'], 'h-4 w-4')
                        {{ $item['label'] }}
                        @if(($item['count'] ?? 0) > 0)
                            <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-indigo-600 px-1.5 text-xs font-semibold text-white">{{ $item['count'] }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </nav>
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

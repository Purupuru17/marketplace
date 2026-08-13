@php
    $user = auth()->user();
    $activeRole = \IdCore\CoreStarter\Support\ActiveRole::get($user);
    $initial = strtoupper(substr($user?->name ?? 'U', 0, 1));
@endphp

<header class="sticky top-0 z-30 w-full bg-white/95 border-b border-gray-200/80 backdrop-blur dark:bg-gray-900/95 dark:border-gray-800">
    <div class="flex items-center justify-between gap-4 px-4 py-3 sm:px-6">
        <div class="flex items-center gap-3">
            <!-- Mobile Sidebar Toggle -->
            <button class="flex lg:hidden items-center justify-center h-10 w-10 text-gray-500 rounded-lg hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
                    @click="$store.layout.toggleSidebar()" aria-label="Toggle Mobile Menu">
                @svg('heroicon-o-bars-3', 'h-5 w-5')
            </button>

            <!-- Desktop Sidebar Collapse Toggle -->
            <button class="hidden lg:flex items-center justify-center h-10 w-10 text-gray-500 rounded-lg hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
                    @click="$store.layout.toggleCollapse()" aria-label="Toggle Sidebar">
                @svg('heroicon-o-bars-3-center-left', 'h-5 w-5')
            </button>

            <!-- Search Bar (Desktop) -->
            <div class="hidden xl:block flex-1 max-w-xl">
                <form action="#" method="GET">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400 dark:text-gray-500">
                            @svg('heroicon-o-magnifying-glass', 'h-5 w-5')
                        </span>
                        <input type="text" name="search" placeholder="Search..."
                               class="h-11 w-72 rounded-lg border border-gray-300 bg-transparent pl-10 pr-3 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    </div>
                </form>
            </div>
        </div>

        <div class="flex items-center gap-2 sm:gap-3">
            <!-- Theme Toggle -->
            <button class="flex items-center justify-center h-10 w-10 text-gray-500 rounded-lg hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
                    @click="$store.theme.toggle()" aria-label="Toggle Theme">
                <span x-show="!$store.theme.dark">@svg('heroicon-o-moon', 'h-5 w-5')</span>
                <span x-show="$store.theme.dark" x-cloak>@svg('heroicon-o-sun', 'h-5 w-5')</span>
            </button>

            <!-- User Dropdown -->
            <div x-data="{ open: false }" class="relative" @click.away="open = false">
                <button class="flex items-center gap-2 rounded-lg p-1.5 hover:bg-gray-100 dark:hover:bg-gray-800"
                        @click.prevent="open = !open" type="button">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-50 text-sm font-bold text-brand-500 dark:bg-gray-800 dark:text-brand-400">
                        {{ $initial }}
                    </span>
                    <span class="hidden sm:block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $user?->name ?? 'Guest' }}</span>
                    @svg('heroicon-o-chevron-down', 'h-4 w-4 text-gray-400 transition-transform duration-200', ['x-bind:class' => "{ 'rotate-180': open }"])
                </button>

                <div x-show="open" x-cloak x-transition
                     class="absolute right-0 z-50 mt-2 w-64 rounded-xl border border-gray-200/80 bg-white p-2 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900">
                    <!-- User Info -->
                    <div class="px-3 py-2.5 border-b border-gray-100 dark:border-gray-800">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $user?->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $user?->email }}</p>
                    </div>

                    <!-- Menu Links -->
                    <div class="py-1 border-b border-gray-100 dark:border-gray-800">
                        <a href="{{ route('dashboard') }}"
                           class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800">
                            @svg('heroicon-o-user', 'h-4 w-4')
                            Edit Profile
                        </a>
                        <a href="{{ route('dashboard') }}"
                           class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800">
                            @svg('heroicon-o-cog-6-tooth', 'h-4 w-4')
                            Account Settings
                        </a>
                    </div>

                    <!-- Role Switcher -->
                    @if($user?->roles?->count() > 1)
                        <div class="py-1 border-b border-gray-100 dark:border-gray-800">
                            <p class="px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Switch Role</p>
                            @foreach($user->roles as $role)
                                <form action="{{ route('switch-role') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="role" value="{{ $role->name }}">
                                    <button type="submit"
                                            class="flex w-full items-center justify-between gap-3 px-3 py-2 text-sm font-medium rounded-lg transition hover:bg-gray-100 dark:hover:bg-gray-800 {{ $activeRole?->id === $role->id ? 'text-brand-500 dark:text-brand-400' : 'text-gray-700 dark:text-gray-400' }}">
                                        <span class="flex items-center gap-3">
                                            @svg('heroicon-o-shield-check', 'h-4 w-4')
                                            <span>{{ $role->name }}</span>
                                        </span>
                                        @if($activeRole?->id === $role->id)
                                            @svg('heroicon-o-check', 'h-4 w-4')
                                        @endif
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    @endif

                    <!-- Sign Out -->
                    <form method="POST" action="{{ route('logout') }}" class="pt-1">
                        @csrf
                        <button type="submit"
                                class="flex w-full items-center gap-3 px-3 py-2 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800">
                            @svg('heroicon-o-arrow-right-on-rectangle', 'h-4 w-4')
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

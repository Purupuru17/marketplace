@php
    $currentPath = request()->path();
@endphp

<aside id="sidebar"
    class="fixed top-0 left-0 z-40 flex h-screen flex-col border-r border-gray-200/80 bg-white/95 shadow-theme-sm backdrop-blur transition-all duration-300 ease-in-out dark:border-gray-800 dark:bg-gray-900/95 lg:sticky"
    x-data="{
        openSubmenus: {},
        toggleSubmenu(key) {
            this.openSubmenus[key] = !this.openSubmenus[key];
        },
        isSubmenuOpen(key) {
            return this.openSubmenus[key] || false;
        }
    }"
    :class="{
        'w-72': !$store.layout.collapsed || $store.layout.sidebarOpen,
        'w-20': $store.layout.collapsed,
        'translate-x-0': $store.layout.sidebarOpen,
        '-translate-x-full lg:translate-x-0': !$store.layout.sidebarOpen
    }">

    <!-- Logo -->
    <div class="flex h-16 items-center border-b border-gray-200/80 px-4 dark:border-gray-800"
        :class="$store.layout.collapsed ? 'lg:justify-center' : ''">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-500 text-sm font-bold text-white shadow-theme-sm">
                {{ substr(config('app.name'), 0, 1) }}
            </div>
            <span x-show="!$store.layout.collapsed" class="text-lg font-bold text-gray-900 dark:text-white truncate">{{ config('app.name') }}</span>
        </a>
    </div>

    <!-- Navigation -->
    <div class="flex-1 overflow-y-auto px-3 py-4 custom-scrollbar">
        <nav>
            <p x-show="!$store.layout.collapsed" class="mb-3 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                Menu
            </p>

            <ul class="space-y-1">
                <!-- Dashboard -->
                <li>
                    <a href="{{ route('dashboard') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-200"
                       :class="[
                           {{ request()->routeIs('dashboard') ? 'true' : 'false' }}
                               ? 'bg-brand-50 text-brand-500 dark:bg-brand-500/[0.12] dark:text-brand-400'
                               : 'text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200',
                           $store.layout.collapsed ? 'lg:justify-center' : ''
                       ]">
                        <span class="shrink-0">@svg('heroicon-o-squares-2x2', 'h-5 w-5')</span>
                        <span x-show="!$store.layout.collapsed">Dashboard</span>
                    </a>
                </li>

                @include('idcore::layouts.partials.sidebar-item', ['items' => $menuTree ?? []])
            </ul>
        </nav>
    </div>
</aside>

<!-- Mobile Overlay -->
<div x-show="$store.layout.sidebarOpen" x-transition.opacity
     @click="$store.layout.toggleSidebar()"
     class="fixed inset-0 z-30 bg-gray-900/50 lg:hidden"></div>

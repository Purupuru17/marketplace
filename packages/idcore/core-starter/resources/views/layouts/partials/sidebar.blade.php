@php
    $currentPath = request()->path();
@endphp

<aside id="sidebar"
    class="fixed flex flex-col top-0 left-0 h-screen bg-white border-r border-gray-200 transition-all duration-300 ease-in-out z-40 dark:bg-gray-900 dark:border-gray-800 lg:sticky"
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
        'w-64': !$store.layout.collapsed || $store.layout.sidebarOpen,
        'w-16': $store.layout.collapsed,
        'translate-x-0': $store.layout.sidebarOpen,
        '-translate-x-full lg:translate-x-0': !$store.layout.sidebarOpen
    }">

    <!-- Logo -->
    <div class="flex items-center h-16 px-4 border-b border-gray-200 dark:border-gray-800"
        :class="$store.layout.collapsed ? 'lg:justify-center' : ''">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary text-sm font-bold text-white">
                {{ substr(config('app.name'), 0, 1) }}
            </div>
            <span x-show="!$store.layout.collapsed" class="text-lg font-bold text-gray-900 dark:text-white truncate">{{ config('app.name') }}</span>
        </a>
    </div>

    <!-- Navigation -->
    <div class="flex-1 overflow-y-auto py-4 px-3 scrollbar-thin scrollbar-thumb-gray-200 dark:scrollbar-thumb-gray-700">
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
                               ? 'bg-blue-50 text-blue-700 dark:bg-gray-800 dark:text-blue-400'
                               : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200',
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

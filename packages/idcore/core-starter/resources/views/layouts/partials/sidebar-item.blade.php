@foreach($items as $item)
    @php $hasChildren = count($item['children']) > 0; @endphp

    @if($hasChildren)
        <li x-data="{ open: @js($item['has_active_child']) }">
            <button type="button" @click.prevent="open = !open"
                    class="flex w-full items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-200"
                    :class="[
                        open ? 'bg-brand-50 text-brand-500 dark:bg-brand-500/[0.12] dark:text-brand-400' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5',
                        $store.layout.collapsed ? 'lg:justify-center' : ''
                    ]">
                <span class="shrink-0">
                    @if($item['icon'])
                        @php $iconName = str_replace('fa fa-', 'heroicon-o-', $item['icon']); @endphp
                        <span class="h-5 w-5">@svg($iconName ?? 'heroicon-o-circle', 'h-5 w-5')</span>
                    @else
                        @svg('heroicon-o-circle', 'h-5 w-5')
                    @endif
                </span>
                <span x-show="!$store.layout.collapsed" class="flex-1 text-left truncate">{{ $item['name'] }}</span>
                <svg x-show="!$store.layout.collapsed"
                     class="h-4 w-4 transition-transform duration-200"
                     :class="{ 'rotate-180': open }"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <div x-show="open && !$store.layout.collapsed" x-collapse>
                <ul class="mt-1 space-y-1 ml-4 border-l border-gray-200/80 pl-3 dark:border-gray-800">
                    @include('idcore::layouts.partials.sidebar-item', ['items' => $item['children']])
                </ul>
            </div>
        </li>
    @else
        <li>
            <a href="{{ url($item['url']) }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-200"
               :class="[
                   {{ $item['is_current'] ? 'true' : 'false' }}
                       ? 'bg-brand-50 text-brand-500 dark:bg-brand-500/[0.12] dark:text-brand-400'
                       : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5',
                   $store.layout.collapsed ? 'lg:justify-center' : ''
               ]">
                <span class="shrink-0">
                    @if($item['icon'])
                        @php $iconName = str_replace('fa fa-', 'heroicon-o-', $item['icon']); @endphp
                        <span class="h-5 w-5">@svg($iconName ?? 'heroicon-o-circle', 'h-5 w-5')</span>
                    @else
                        @svg('heroicon-o-circle', 'h-5 w-5')
                    @endif
                </span>
                <span x-show="!$store.layout.collapsed" class="truncate">{{ $item['name'] }}</span>
            </a>
        </li>
    @endif
@endforeach

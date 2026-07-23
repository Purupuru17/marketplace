@foreach($items as $item)
    @php
        $isGroup = empty($item['url']) || $item['url'] === '#';
        $isLast = $loop->last;
        $indent = $depth * 24;
    @endphp

    <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/60">
        <td class="px-6 py-4 relative">
            @if($depth > 0)
                <span class="absolute left-0 top-0 w-px bg-gray-300 dark:bg-gray-600" style="left: {{ $indent - 10 }}px; height: {{ $isLast ? '50%' : '100%' }};"></span>
                <span class="absolute left-0 top-1/2 w-3 h-px bg-gray-300 dark:bg-gray-600" style="left: {{ $indent - 10 }}px;"></span>
            @endif
            <div class="flex items-center gap-3" style="padding-left: {{ $depth > 0 ? $indent + 8 : $indent }}px;">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $isGroup ? 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' : 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300' }}">
                    @php
                        $iconName = $item['icon'] ?: ($isGroup ? 'heroicon-o-folder' : 'heroicon-o-circle');
                        $iconName = str_replace('fa fa-', 'heroicon-o-', $iconName);
                    @endphp
                    @svg($iconName, 'h-5 w-5')
                </div>
                <div class="min-w-0">
                    <p class="truncate font-semibold {{ $isGroup ? 'text-gray-700 dark:text-gray-200' : 'text-gray-900 dark:text-white' }}">{{ $item['name'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $isGroup ? 'Group menu' : 'Menu link' }}</p>
                </div>
            </div>
        </td>

        <td class="px-6 py-4 text-gray-600 dark:text-gray-300 hidden md:table-cell">
            @if($isGroup)
                <span class="text-gray-400">-</span>
            @else
                <code class="rounded-md bg-gray-100 px-2 py-1 text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ $item['url'] }}</code>
            @endif
        </td>

        <td class="px-6 py-4 hidden lg:table-cell">
            <div class="flex flex-wrap gap-1.5">
                @forelse($item['actions'] ?? [] as $action)
                    <x-idcore::badge variant="blue">{{ $action }}</x-idcore::badge>
                @empty
                    <span class="text-sm text-gray-400">-</span>
                @endforelse
            </div>
        </td>

        <td class="px-6 py-4 text-center">
            @if($item['is_active'])
                <x-idcore::badge variant="green">Aktif</x-idcore::badge>
            @else
                <x-idcore::badge>Nonaktif</x-idcore::badge>
            @endif
        </td>

        <td class="px-6 py-4 text-right">
            <div class="flex items-center justify-end gap-1">
                @can('menu.edit')
                    <a href="{{ route('sistem.menu.edit', $item['id']) }}"
                       class="inline-flex h-7 w-7 items-center justify-center rounded-full text-blue-600 transition hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-500/10">
                        @svg('heroicon-o-pencil-square', 'h-3.5 w-3.5')
                    </a>
                @endcan
                @can('menu.delete')
                    <button type="button" x-data
                            @click.prevent="
                                $confirm({
                                    title: 'Hapus Menu?',
                                    message: 'Menu {{ $item['name'] }} akan dihapus permanen.',
                                    confirmText: 'Ya, Hapus',
                                    variant: 'danger'
                                }).then(ok => { if (ok) $el.nextElementSibling.submit(); });
                            "
                            class="inline-flex h-7 w-7 items-center justify-center rounded-full text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10">
                        @svg('heroicon-o-trash', 'h-3.5 w-3.5')
                    </button>
                    <form action="{{ route('sistem.menu.destroy', $item['id']) }}" method="POST" class="hidden">
                        @csrf @method('DELETE')
                    </form>
                @endcan
            </div>
        </td>
    </tr>

    @if(count($item['children']))
        @include('idcore::sistem.menu.partials.tree-item', ['items' => $item['children'], 'depth' => $depth + 1])
    @endif
@endforeach

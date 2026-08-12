@props(['items' => []])

<div class="mt-2 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
    @foreach($items as $item)
        @if(!$loop->last)
            <a href="{{ $item[1] ?? '#' }}" class="hover:text-primary-600 dark:hover:text-primary-400">{{ $item[0] }}</a>
            <span class="text-gray-300 dark:text-gray-600">/</span>
        @else
            <span>{{ $item[0] }}</span>
        @endif
    @endforeach
</div>

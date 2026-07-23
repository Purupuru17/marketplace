@props(['items' => []])

<div class="mt-2 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
    @foreach($items as $item)
        @if(!$loop->last)
            <a href="{{ $item['url'] ?? '#' }}" class="hover:text-brand-600">{{ $item['label'] }}</a>
            <span class="text-gray-300 dark:text-gray-600">/</span>
        @else
            <span>{{ $item['label'] }}</span>
        @endif
    @endforeach
</div>

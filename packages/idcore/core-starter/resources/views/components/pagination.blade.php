@props(['paginator'])

@if($paginator->hasPages())
<div class="flex flex-col items-center justify-between gap-3 border-t border-gray-100 px-5 py-4 text-sm dark:border-gray-800 sm:flex-row">
    <div class="text-gray-500 dark:text-gray-400">
        Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} entries
    </div>

    <div class="flex items-center gap-1">
        @foreach($paginator->linkCollection() as $link)
            @php
                $label = $link['label'];
                $label = str_contains($label, 'Previous') ? 'Previous' : (str_contains($label, 'Next') ? 'Next' : $label);
            @endphp
            @if($link['url'] === null)
                <span class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-200 px-3 text-xs font-medium text-gray-300 dark:border-gray-800 dark:text-gray-600">{!! $label !!}</span>
            @elseif($link['active'])
                <span class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg bg-primary-600 px-3 text-xs font-semibold text-white shadow-theme-xs">{!! $label !!}</span>
            @else
                <a href="{{ $link['url'] }}" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-gray-200/80 bg-white px-3 text-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">{!! $label !!}</a>
            @endif
        @endforeach
    </div>
</div>
@endif

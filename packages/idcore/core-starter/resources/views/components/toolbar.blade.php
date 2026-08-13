@props([
    'title' => null,
    'subtitle' => null,
    'searchable' => true,
    'searchName' => 'search',
    'searchValue' => null,
    'searchPlaceholder' => 'Search...',
    'action' => null,
    'method' => 'GET',
])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between']) }}>
    <div class="flex flex-1 flex-wrap items-center gap-2">
        @if($title)
            <h2 class="text-base font-medium text-gray-800 dark:text-white/90">{{ $title }}</h2>
        @endif
        @if(isset($filters))
            {{ $filters }}
        @endif
    </div>

    @if($searchable)
        <form action="{{ $action ?: url()->current() }}" method="{{ $method }}" class="relative w-full sm:max-w-xs">
            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400">@svg('heroicon-o-magnifying-glass', 'h-4 w-4')</span>
            <x-idcore::input name="{{ $searchName }}" type="search" value="{{ $searchValue }}" placeholder="{{ $searchPlaceholder }}" class="pr-8" onchange="this.form.submit()" />
        </form>
    @endif
</div>
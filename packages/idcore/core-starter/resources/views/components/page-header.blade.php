@props([
    'title' => null,
    'subtitle' => null,
    'breadcrumb' => [],
])

<div {{ $attributes->merge(['class' => 'mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between']) }}>
    <div>
        @if(!empty($breadcrumb))
            <x-idcore::breadcrumb :items="$breadcrumb" class="mt-0 [&>*:first-child]:mt-0" />
        @endif
        @if($title)
            <h1 class="{{ !empty($breadcrumb) ? 'mt-2' : '' }} text-2xl font-bold text-gray-900 dark:text-white">{{ $title }}</h1>
        @endif
        @if($subtitle)
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $subtitle }}</p>
        @endif
    </div>
    @if(isset($actions))
        <div class="flex shrink-0 items-center gap-2">{{ $actions }}</div>
    @endif
</div>
@props(['striped' => false])

<div class="rounded-xl border border-gray-200/80 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    <div class="max-w-full overflow-x-auto">
        <table {{ $attributes->merge(['class' => 'w-full table-auto']) }}>
            {{ $slot }}
        </table>
    </div>
</div>

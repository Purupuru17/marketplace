@props(['striped' => false])

<div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
    <div class="max-w-full overflow-x-auto">
        <table {{ $attributes->merge(['class' => 'w-full table-auto']) }}>
            {{ $slot }}
        </table>
    </div>
</div>

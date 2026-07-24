@props(['striped' => false])

<div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-700 dark:bg-white/[0.03]">
    <div class="max-w-full overflow-x-auto">
        <table {{ $attributes->merge(['class' => 'w-full table-auto']) }}>
            {{ $slot }}
        </table>
    </div>
</div>

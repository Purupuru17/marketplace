@props(['striped' => false])

<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="max-w-full overflow-x-auto">
        <table {{ $attributes->merge(['class' => 'min-w-full']) }}>
            {{ $slot }}
        </table>
    </div>
</div>

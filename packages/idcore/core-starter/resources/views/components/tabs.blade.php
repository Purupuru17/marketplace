@props(['default'])

<div x-data="{ tab: '{{ $default }}' }">
    {{ $slot }}
</div>

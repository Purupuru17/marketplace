@props(['value'])

<div x-show="tab === '{{ $value }}'" x-cloak>
    {{ $slot }}
</div>

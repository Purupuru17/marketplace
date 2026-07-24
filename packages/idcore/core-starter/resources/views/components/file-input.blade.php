@props([
    'name',
    'label' => null,
    'accept' => null,
    'hint' => null,
    'required' => false,
    'preview' => null,
])

@php
    $hasError = $errors->has($name);
    $inputId = 'file-' . str_replace(['[', ']', '.'], '-', $name);
@endphp

<div x-data="{ fileName: null, previewUrl: @js($preview) }">
    @if($label)
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            {{ $label }} @if($required)<span class="text-danger-500">*</span>@endif
        </label>
    @endif

    <label for="{{ $inputId }}" class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed px-6 py-8 text-center transition hover:border-brand-400 hover:bg-brand-50/40 dark:border-gray-700 dark:hover:border-brand-500 dark:hover:bg-brand-500/10 {{ $hasError ? 'border-danger-300' : 'border-gray-200 dark:border-gray-700' }}">
        <template x-if="previewUrl">
            <img :src="previewUrl" class="mb-3 h-16 w-16 rounded-lg object-cover">
        </template>
        <template x-if="!previewUrl">
            <span class="mb-3 inline-flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-300">
                @svg('heroicon-o-cloud-arrow-up', 'h-6 w-6')
            </span>
        </template>
        <span class="text-sm font-semibold text-gray-800 dark:text-white" x-text="fileName || 'Drag & Drop File Here'"></span>
        <span class="mt-1 text-xs text-gray-500 dark:text-gray-400">Drag and drop file here or browse</span>
        <input type="file" id="{{ $inputId }}" name="{{ $name }}" @if($accept) accept="{{ $accept }}" @endif class="hidden"
               @change="fileName = $event.target.files[0]?.name; if ($event.target.files[0]) previewUrl = URL.createObjectURL($event.target.files[0])">
    </label>

    @if($hint && !$hasError)
        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="mt-1.5 text-xs text-danger-600 dark:text-danger-500">{{ $message }}</p>
    @enderror
</div>

@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'hint' => null,
    'required' => false,
    'leftIcon' => null,
    'leftText' => null,
    'leftOptions' => [],
    'rightButtonText' => null,
    'rightIcon' => null,
])

@php
    $hasError = $errors->has($name);
    $inputId = 'input-group-' . str_replace(['[', ']', '.'], '-', $name);
    $oldKey = str_replace(['[', ']'], ['.', ''], $name);

    if (is_string($leftOptions)) {
        $pairs = array_map(fn ($opt) => explode('=', $opt, 2), array_filter(array_map('trim', explode('|', $leftOptions))));
        $leftOptions = collect($pairs)->mapWithKeys(fn ($pair) => [$pair[0] => $pair[1] ?? $pair[0]])->all();
    }

    $hasLeft = $leftIcon || $leftText || count($leftOptions) > 0;
    $hasRight = $rightButtonText || $rightIcon;

    $borderClasses = $hasError
        ? 'border-error-300 focus-within:border-error-300 focus-within:ring-error-500/10 dark:border-error-700'
        : 'border-gray-300 focus-within:border-brand-300 focus-within:ring-brand-500/10 dark:border-gray-700';
@endphp

<div>
    @if($label)
        <label for="{{ $inputId }}" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            {{ $label }} @if($required)<span class="text-error-500">*</span>@endif
        </label>
    @endif

    <div class="flex h-11 min-w-0 items-stretch overflow-hidden rounded-lg border bg-transparent shadow-theme-xs transition focus-within:ring-3 focus-within:outline-hidden {{ $borderClasses }} dark:bg-gray-900">
        @if($leftIcon)
            <span class="pointer-events-none flex shrink-0 items-center border-r border-gray-200 px-3 text-gray-400 dark:border-gray-800 dark:text-gray-500">
                @svg('heroicon-o-' . $leftIcon, 'h-5 w-5')
            </span>
        @endif

        @if($leftText)
            <span class="flex shrink-0 items-center border-r border-gray-200 px-3 text-sm font-medium text-gray-500 dark:border-gray-800 dark:text-gray-400">{{ $leftText }}</span>
        @endif

        @if(count($leftOptions) > 0)
            <div class="relative flex shrink-0 items-stretch border-r border-gray-200 dark:border-gray-800">
                <select name="{{ $name }}_prefix" class="h-full w-full appearance-none border-0 bg-transparent py-2.5 pl-3 pr-8 text-sm text-gray-700 shadow-none outline-none transition focus:ring-0 dark:bg-gray-900 dark:text-white/90">
                    @foreach($leftOptions as $value => $text)
                        <option value="{{ $value }}" class="text-gray-800 dark:text-gray-200">{{ $text }}</option>
                    @endforeach
                </select>
                <span class="pointer-events-none absolute inset-y-0 right-2 flex items-center text-gray-400 dark:text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </span>
            </div>
        @endif

        <input type="{{ $type }}"
               id="{{ $inputId }}"
               name="{{ $name }}"
               value="{{ old($oldKey, $value) }}"
               placeholder="{{ $placeholder }}"
               @if($required) required @endif
               {{ $attributes->merge(['class' => 'h-full min-w-0 flex-1 border-0 bg-transparent px-3 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 shadow-none outline-none ring-0 focus:border-0 focus:outline-none focus:ring-0 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:disabled:bg-gray-800 dark:disabled:text-gray-400 ' . ($hasRight ? 'pr-3 ' : 'pr-4 ')]) }}>

        @if($rightButtonText || $rightIcon)
            <button type="button"
                    class="inline-flex shrink-0 items-center gap-2 border-l border-gray-200 bg-gray-50 px-3 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-800 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10">
                @if($rightIcon)
                    @svg('heroicon-o-' . $rightIcon, 'h-4 w-4')
                @endif
                {{ $rightButtonText }}
            </button>
        @endif
    </div>

    @if($hint && !$hasError)
        <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="mt-1.5 text-xs text-error-500">{{ $message }}</p>
    @enderror
</div>
@props([
'label',
'for',
'error' => false,
'helpText' => false,
'helptextinline' => false,
'inline' => false,
'paddingless' => false,
'borderless' => false,
])

@if($inline)
    <div>
        <label for="{{ $for }}" class="flex space-x-1 text-sm font-medium leading-5 text-gray-700">
            <span>{{ $label }}</span>
            @if($helptextinline)
                <span>{{ $helpText }}</span>
            @endif
        </label>

        <div {{$attributes->merge(['class'=> 'mt-1 relative rounded-md'])}}>
            {{ $slot }}

            @if ($error)
                <div class="mt-1 text-red-500 text-sm">{{ $error }}</div>
            @endif

            @if ($helpText && !$helptextinline)
                <span class="text-xs font-light italic text-gray-500">{{ $helpText }}</span>
            @endif
        </div>
    </div>
@else
    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start {{ $borderless ? '' : ' sm:border-t ' }} sm:border-gray-200 {{ $paddingless ? '' : ' sm:py-5 ' }}">
        <label for="{{ $for }}" class="block text-sm font-medium leading-5 text-gray-700 sm:mt-px sm:pt-2">
            {{ $label }}
        </label>

        <div class="mt-1 sm:mt-0 sm:col-span-2">
            {{ $slot }}

            @if ($error)
                <div class="mt-1 text-red-500 text-sm">{{ $error }}</div>
            @endif

            @if ($helpText)
                <p class="mt-2 text-sm text-gray-500">{{ $helpText }}</p>
            @endif
        </div>
    </div>
@endif
@props([
    'placeholder' => null,
    'trailingAddOn' => null,
])

<div class="flex">
    <select {{ $attributes->merge(['class' => 'input duration-300 ease-in-out focus:ring-0 pl-3 pr-10 rounded shadow-sm sm:text-sm text-base transition w-full' . ($trailingAddOn ? ' rounded-r-none' : '')]) }}>
        @if ($placeholder)
            <option disabled value="">{{ $placeholder }}</option>
        @endif

        {{ $slot }}
    </select>

    @if ($trailingAddOn)
        {{ $trailingAddOn }}
    @endif
</div>

@props([
    'route'=>'',
])
<div x-data="{open:false}" x-init="open = {{ request()->is($route) ? 'true' : 'false' }}">
    <button
        class="{{ (request()->is($route)) ? 'text-white' : 'text-lhg-gray-25 hover:text-lhg-gray-12' }} group text-left ease-in-out duration-150 group flex md:flex-col xl:flex-row p-3 px-2 py-2 md:text-xs xl:text-sm font-medium rounded-md items-center w-full"
        type="button"
        :class="{ 'text-white': open, 'text-lhg-gray-25': !(open) }"
        @click="open = !open"
        x-cloak
    >
        {{ $icon }}
        <span class="flex-1 md:text-center xl:text-start">{{ $title }}</span>
        <span>
        <x-icon.chevron-down x-show="open" class="md:hidden xl:block" size="5"/>
        <x-icon.chevron-up x-show="!open" class="md:hidden xl:block" size="5"/>
    </span>
    </button>
    <div x-show="open" x-collapse class=" md:items-center">
        {{ $slot }}
    </div>
</div>

@props([
    'type'=>null,
    'title'=>'Change me!',
    'hCenter'=>false
    ])
<div
        x-show="open" x-transition
        x-on:click="open = false"
        class="relative flex items-center justify-center px-1 py-1 sm:px-4 sm:py-8 "
        :class="'{{ $hCenter }}' ? 'min-h-screen' : ''"
>
    <div
            x-on:click.stop
            x-trap.noscroll.inert="open"
            {{ $attributes->merge(['class'=>'relative w-full bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 overflow-y-auto']) }}
    >
        <!-- Title -->
        <div class="sm:flex sm:items-center sm:justify-start space-x-2">
            @if($type=='warning')
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                    <x-icon.warning class="text-red-600" size="6"/>
                </div>
            @elseif($type=='info')
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                    <x-icon.info class="text-lhg-blue" size="6"/>
                </div>
            @endif
            <h3 class="text-center sm:text-left" :id="$id('modal-title')">{{$title}}</h3>
        </div>
        <!-- Content -->
        <p class="mt-2">
            {{ $content }}
        </p>
        <!-- Buttons -->
        <div class="mt-8 flex justify-end items-center space-x-2">
            <span wire:loading.class.remove="hidden" class="hidden animate-pulse text-gray-500 dark:text-gray-300">Loading ...</span>
            {{ $button }}
        </div>
    </div>
</div>
@props(['type'=>null, 'title'=>'Change me!'])
<div
        x-show="open" x-transition
        x-on:click="open = false"
        class="relative min-h-screen flex items-center justify-center p-4"
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
            @endif
            <h3 class="text-center sm:text-left" :id="$id('modal-title')">{{$title}}</h3>
        </div>
        <!-- Content -->
        <p class="mt-2 text-gray-600">
            {{ $content }}
        </p>
        <!-- Buttons -->
        <div class="mt-8 flex justify-end space-x-2">
            {{ $button }}
        </div>
    </div>
</div>
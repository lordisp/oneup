@props([
'buttons' => false,
'search' => false,
'id' => 'search',
'paddingless' => false,
'justify' => false,
'contrast' => false,
])

<div @click.away="search = false" {{ $attributes->merge(['class' => 'shadow-md overflow-hidden rounded-md']) }}>
    <div class="{{$contrast ? 'bg-gray-50' : 'bg-white dark:bg-gray-800'}}">
        @if($search)
            <div class="flex {{$justify ? "justify-".$justify : "justify-end"}}">
                <div x-show="search"
                     x-transition:enter="transform transition ease-in duration-300 sm:duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transform transition ease-out duration-300 sm:duration-300"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="w-full pt-2">
                    <x-input.search wire:model.debounce="{{$id}}" placeholder="Search ..." id="{{$id}}" autocomplete="off"/>
                </div>
                <button
                        x-show="!search"
                        @click.prevent="search = ! search "
                        @keyup.escape.document="search = false"
                        class="mt-1 mr-1 focus:outline-none"
                >
                    <x-icon.search solid class="text-gray-300 hover:text-gray-400"/>
                </button>
            </div>
        @endif
        <div class="{{$search ? 'flex' : ''}} {{$paddingless ? 'py-1 md:py-4' : 'px-4 py-4'}} ">
            {{$slot}}
        </div>
        @if($buttons)
            <div class="{{$contrast ? 'bg-gray-200 dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-900'}} border-gray-100 dark:border-gray-700 items-center border-t flex flex-col sm:flex-row justify-between sm:justify-end mt-6 px-4 py-4 space-y-2 sm:space-y-0 sm:space-x-2">
                {{$buttons}}
            </div>
        @endif
    </div>
</div>
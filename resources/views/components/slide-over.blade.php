@props([
'show' => 'open',
'withoutSave' => false,
'submit' => 'Save',
'cancel' => 'Cancel',
'clickAway' => false,
])
<div x-show="{{$show}}"
     class="z-10 fixed inset-0  overflow-hidden" aria-labelledby="slide-over-title" x-ref="dialog" aria-modal="true">
    <div class="absolute inset-0 overflow-hidden">
        <div x-description="Background overlay, show/hide based on slide-over state." class="absolute inset-0" aria-hidden="true"></div>
        <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
            <div x-show="{{$show}}"
                 x-transition:enter="transform transition ease-in-out duration-300 sm:duration-500"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in-out duration-300 sm:duration-500"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full" class="w-screen max-w-md"
                 @if($clickAway)@mousedown.outside="{{$show}} = false" @endif
                 x-cloak>
                <div class="h-2 bg-lhg-blue"></div>
                <div {{$attributes->merge(['class' => 'h-full divide-y divide-gray-200 flex flex-col bg-white shadow-xl'])}}>
                    <div class="min-h-0 flex-1 flex flex-col py-6 overflow-y-scroll">
                        <div class="px-4 sm:px-6">
                            <div class="flex items-start justify-between">
                                <h2 id="slide-over-title">
                                    {{$title}}
                                </h2>
                                <div class="ml-3 h-7 flex items-center">
                                    <button wire:click.prevent="resetPage" @click.prevent="{{$show}} = false"
                                            class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-lh-yellow">
                                        <span class="sr-only">Close panel</span>
                                        <svg class="h-6 w-6" x-description="Heroicon name: outline/x" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="mt-6 relative flex-1 px-4 sm:px-6">
                            {{$content}}
                        </div>
                    </div>
                    <div class="flex-shrink-0 px-4 py-4 flex justify-end space-x-2 bg-white">
                        <x-btn.cancel wire:click.prevent="resetPage" @click.prevent="{{$show}} = false">
                            {{$cancel}}
                        </x-btn.cancel>
                        @if($withoutSave===false)
                            <x-btn.primary type="submit" @click="{{$show}} = false">
                                {{$submit}}
                            </x-btn.primary>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
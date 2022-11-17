@if ($paginator->hasPages())
    <nav class="flex items-center justify-between sm:px-0 mb-4">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <div class="-mt-px flex-1 flex invisible">
                <span class="border-t-2 border-transparent border-lhg-blue pt-4 pr-1 inline-flex items-center text-sm font-medium >
                    <x-icon.chevron-left/>
                </span>
            </div>
        @else
            <div class="-mt-px flex-1 flex justify-start items-center">
                <button wire:click.prevent="previousPage" type="button"
                        class="border-t-2 border-transparent md:hover:border-gray-300 pt-4 pr-1 inline-flex items-center text-sm font-medium ">
                    <x-icon.chevron-left solid/>
                    <span class="hidden sm:block">{{trans('pagination.previous')}}</span>
                </button>
            </div>
        @endif
        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <div class="-mt-px flex-1 flex justify-end items-center">
                {{-- go to next page --}}
                <button wire:click.prevent="nextPage" type="button"
                        class="border-t-2 border-transparent pt-4 pl-1 inline-flex items-center text-sm font-medium md:hover:border-gray-300">
                    <span class="hidden sm:block">{{trans('pagination.next')}}</span>
                    <x-icon.chevron-right solid/>
                </button>
            </div>
        @else
            <div class="-mt-px flex-1 flex justify-end invisible">
                <span class="border-t-2 border-transparent pt-4 pl-1 inline-flex items-center text-sm font-medium border-lhg-blue">
                    <x-icon.chevron-right/>
                </span>
            </div>
        @endif
    </nav>
@endif

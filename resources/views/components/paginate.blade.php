@if ($paginator->hasPages())
    <nav class="flex md:border-t border-lhg-gray-25 dark:border-lhg-gray-60 px-4 items-center justify-between sm:px-0 mb-4">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <div class="-mt-px flex-1 flex invisible">
                <span class="border-t-2 border-transparent border-lhg-blue pt-4 pr-1 inline-flex items-center text-sm font-medium >
                    <x-icon.chevron-left/>
                </span>
            </div>
        @else
            <div class="-mt-px flex-1 flex justify-start items-center">
                <button wire:click.prevent="gotoPage(1)" type="button"
                        class="border-t-2 border-transparent md:hover:border-gray-300 pt-4 pl-1 inline-flex items-center text-sm font-medium ">
                    <x-icon.chevron-double-left size="4" solid/>
                </button>
                <button wire:click.prevent="previousPage" type="button"
                        class="border-t-2 border-transparent md:hover:border-gray-300 pt-4 pr-1 inline-flex items-center text-sm font-medium ">
                    <x-icon.chevron-left solid/>
                    <span class="hidden sm:block">{{trans('pagination.previous')}}</span>
                </button>
            </div>
        @endif

        {{-- Pagination Elements --}}
        <div class="flex -mt-px">
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="hidden md:block border-transparent border-t-2 pt-4 px-4 inline-flex items-center text-sm font-medium"> ... </span>
                @endif
                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="border-transparent md:border-lhg-blue dark:md:border-lhg-gray-6 border-t-2 pt-4 px-4 inline-flex items-center text-sm font-bold" aria-current="page"><span class="md:hidden">Page&nbsp;</span> {{ $page }} <span
                                    class="md:hidden">&nbsp;/{{$paginator->lastPage()}}</span></span>
                        @else
                            <button wire:click.prevent="gotoPage({{ $page }})" type="button"
                                    class="hidden md:block border-transparent hover:border-gray-300 border-t-2 pt-4 px-4 inline-flex items-center text-sm font-medium">{{ $page }} </button>
                        @endif
                    @endforeach
                @endif

            @endforeach
        </div>

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <div class="-mt-px flex-1 flex justify-end items-center">
                {{-- go to next page --}}
                <button wire:click.prevent="nextPage" type="button"
                        class="border-t-2 border-transparent pt-4 pl-1 inline-flex items-center text-sm font-medium md:hover:border-gray-300">
                    <span class="hidden sm:block">{{trans('pagination.next')}}</span>
                    <x-icon.chevron-right solid/>
                </button>
                {{-- go to last page --}}
                <button wire:click.prevent="gotoPage({{$paginator->lastPage()}})" type="button"
                        class="border-t-2 border-transparent pt-4 pl-1 inline-flex items-center text-sm font-medium md:hover:border-gray-300">
                    <x-icon.chevron-double-right size="4" solid/>
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

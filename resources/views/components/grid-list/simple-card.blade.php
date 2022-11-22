@props([
'colour' => false,
'lines' => 1,
'withButton' => false,
'active' => false
])
<li class="{{$active ?: ''}} col-span-1 h-12 flex focus:ring-1 h-12 transition ease-in-out duration-300 rounded-md shadow-md hover:shadow-xl">
    <div class="flex-shrink-0 flex items-center justify-center w-12 {{$colour}} text-white text-sm font-medium rounded-l-md">
        {{$int}}
    </div>
    <div class="flex-1 flex items-center justify-between border-t border-r border-b border-gray-200 bg-white rounded-r-md truncate">
        <div class="flex-1 px-4 py-2 text-sm truncate">
            <a {{$attributes}} class="text-gray-900 font-medium hover:text-gray-600 {{$attributes ? 'cursor-pointer':''}}">{{$slot}}</a>
            @if($lines>1)
                <p class="text-gray-500">{{$line}}</p>
            @endif
        </div>
        @if($withButton)
            <div class="flex-shrink-0 pr-2">
                <button class="w-8 h-8 bg-white inline-flex items-center justify-center text-gray-400 rounded-full bg-transparent hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <span class="sr-only">Open options</span>
                    <!-- Heroicon name: solid/dots-vertical -->
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                    </svg>
                </button>
            </div>
        @endif
    </div>
</li>
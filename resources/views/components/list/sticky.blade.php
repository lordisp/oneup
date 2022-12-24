@props([
'image' => false,
'second' => false,
'cta' => false,
'link' => false,
'size' => false,
'class' => false,
'tag' => 'span'
])
<li>

    <div class="flex focus-within:ring-inset {{$class ? $class : null}} focus:bg-gray-50 hover:bg-gray-50 dark:hover:bg-gray-600 items-center px-6 {{$size ? "py-". $size : "py-5"}}  relative space-x-3">

        @if($image)
            <div class="flex-shrink-0">
                {{$image}}
            </div>
        @endif
        <div class="flex-1 min-w-0 z-10 ">
            <{{$tag}} {{$attributes->merge(['class'=>'focus:outline-none'])}}>
            <span class="absolute inset-0 " aria-hidden="true"></span>
            <div class="text-sm font-medium text-gray-900 dark:text-white ">
                {{$first}}
            </div>
            @if($second)
                <div class="{{$size<5 ? "text-xs" : "text-sm" }} text-gray-300 truncate">
                    {{$second}}
                </div>
            @endif
            @if($link)
                <div class="{{$size<5 ? "text-xs" : "text-sm" }} text-gray-300 truncate">
                    {{$link}}
                </div>
            @endif

        </{{$tag}}>
    </div>
    @if($cta)
        <span class="text-gray-500 inset-0 z-10">
            {{$cta}}
        </span>
    @endif
</li>
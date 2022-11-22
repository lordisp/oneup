@props([
'image' => false,
'secondline' => false,
'size' => false,
'class' => false,
'tag' => 'span'
])
<li >
    <div class="flex focus-within:ring-inset {{$class ? $class : null}} focus:bg-gray-50 hover:bg-gray-50 items-center px-6 {{$size ? "py-". $size : "py-5"}}  relative space-x-3">
        @if($image)
            <div class="flex-shrink-0">
                {{$image}}
            </div>
        @endif
        <div class="flex-1 min-w-0">
            <{{$tag}} {{$attributes->merge(['class'=>'cursor-pointer focus:outline-none'])}}>
                <span class="absolute inset-0 " aria-hidden="true"></span>
                <div class="text-sm font-medium text-gray-900 ">
                    {{$first}}
                </div>
                @if($secondline)
                <div class="{{$size<5 ? "text-xs" : "text-sm" }} text-gray-500 truncate">
                    {{$second}}
                </div>
                @endif
            </{{$tag}}>
        </div>
    </div>
</li>
@props([
'notify' => false,
'cols' => 3,
])

@php
switch($cols){
    case 4:
    $colCss ='md:grid sm:grid-cols-4 sm:gap-6 md:grid-cols-1 md:gap-0 lg:grid-cols-4 lg:gap-6';
    $spanCss ='mt-5 md:mt-0 md:col-span-3';
break;
default:
    $colCss ='md:grid sm:grid-cols-3 sm:gap-6 md:grid-cols-1 md:gap-0 lg:grid-cols-3 lg:gap-6';
    $spanCss ='mt-5 md:mt-0 md:col-span-2';
}
@endphp
<div class="px-4 py-5 sm:rounded-lg sm:p-6">
    <div class="{{$colCss}}">
        <div class="md:col-span-1 flex">
            <div>
                <h3 class="text-lg font-medium leading-6 text-gray-900">{{$title}}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-white">
                    {{$subtitle}}
                </p>
            </div>
            @if($notify)
                <div class="px-4">
                    {{$notify}}
                </div>
            @endif
        </div>

        <div {{$attributes}} class="{{$spanCss}}" x-data="{ open: false, search: false }" x-cloak>
            {{$slot}}
        </div>
    </div>
</div>
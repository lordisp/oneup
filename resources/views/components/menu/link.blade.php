@props([
    'request' => '',
])

@php
    $class = (request()->is($request)) ? 'text-white bg-lhg-ui-GreyBlue' : 'text-lhg-gray-25 hover:text-lhg-gray-12'
@endphp
<a {{$attributes->merge(['class'=> $class. ' flex group md:flex-col xl:flex-row md:text-xs xl:text-sm p-2 rounded-md text-sm py-2 my-1 items-center ease-in-out duration-300'])}}>
    {{ $icon }}
    <span class="md:mt-2 xl:mt-0">{{ $slot }}</span>
</a>

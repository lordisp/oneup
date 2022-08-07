@props([
'type' => 'text',
'leadingAddOn' => false,
])
@php
    if($leadingAddOn){
        $class="focus:ring-lhg-yellow focus:border-lhg-yellow dark:bg-gray-400 dark:focus:border-yellow-50 block shadow-sm sm:text-sm border-gray-300 rounded-r-md transition duration-300 ease-in-out";
    }else{
        $class="focus:ring-lhg-yellow focus:border-lhg-yellow dark:bg-gray-400 dark:focus:border-yellow-50 block shadow-sm sm:text-sm border-gray-300 rounded-md transition duration-300 ease-in-out";
    }
@endphp
<div class="flex rounded-md shadow-sm justify-center md:justify-start">
    @if ($leadingAddOn)
        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
            {{ $leadingAddOn }}
        </span>
    @endif
    <input {{ $attributes->merge(['class' => $class])}}  type="{{ $type }}">
</div>
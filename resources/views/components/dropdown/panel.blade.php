@props([
    'pos'=>'right',
    'wide' => 'w-56'
])
@php
    $class = match($pos){
        'left'=>'origin-top-left left-0',
        default=>'origin-top-right right-0',
    };
@endphp
<div
    x-ref="panel"
    x-show="open"
    x-transition.origin.top.right
    x-on:click.outside="close($refs.button)"
    :id="$id('dropdown-button')"
    style="display: none;"
    class="{{$class .' '.$wide}} right-50 z-auto absolute mt-2 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black dark:ring-gray-400 ring-opacity-5 focus:outline-none"
    role="menu" aria-orientation="vertical"
    aria-labelledby="menu-button"
    tabindex="-1"
>
    <div {{ $attributes->merge(['class'=>'py-1']) }} role="none">
        {{$slot}}
    </div>
</div>

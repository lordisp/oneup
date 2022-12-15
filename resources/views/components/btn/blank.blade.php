@props(['active' => true, 'sr'=>'Options', 'color'=>'bg-white'])
<button @if(!$active) disabled @endif {{$attributes->merge(['type'=>'submit', 'class'=>'inline-flex justify-center px-3.5 py-2 border border-lhg-gray-12 dark:border-slate-600 dark:bg-slate-600  dark:focus:border-slate-500 selection:bg-lhg-blue selection:text-white  dark:selection:bg-lhg-gray-12 dark:selection:text-lhg-blue shadow-sm text-sm font-medium rounded-md focus:outline-none transition duration-300 ease-in-out '.$color])}}>
    {{$slot}}
    <span class="sr-only">{{$sr}}</span>
</button>
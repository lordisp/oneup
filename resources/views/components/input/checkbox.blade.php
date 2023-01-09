@props(['disabled'=>false])
<div class="flex rounded-md shadow-sm max-w-0">
    <input {{ $attributes->merge(['class'=> 'border-gray-300 dark:bg-transparent focus:ring-lhg-yellow h-4 rounded text-lhg-yellow w-4 transition duration-150 ease-in-out']) }}
           type="checkbox" {{$disabled ? 'disabled' : ''}}
    />
</div>
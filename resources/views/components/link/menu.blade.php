<a {{$attributes->merge(['class'=>'text-indigo-100 group flex md:flex-col xl:flex-row p-3 px-2 py-2 md:text-xs xl:text-sm font-medium rounded-md items-center w-full'])}}>
    {{ $icon }}
    <span class="md:mt-2 xl:mt-0">{{ $slot }}</span>
</a>

@props([
    'request' => ''
])
<a {{ $attributes }}
   class="{{(request()->is($request)) ? 'bg-lhg-ui-GreyBlue text-white' : 'hover:translate-x-1.5'}}  flex md:justify-center xl:justify-start text-lhg-gray-12 text-sm md:text-xs xl:text-sm pl-11 md:pl-0 xl:pl-11 py-2 my-1 ease-in-out duration-300 rounded-md">
    {{ $slot }}
</a>

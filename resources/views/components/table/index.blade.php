@props(['rounded' => true])
<div x-data="{ stick: false}"
     @scroll.window="document.documentElement.scrollTop > 99 ? stick = true : stick = false"
     class="-my-2 -mx-4 sm:-mx-6 lg:-mx-8"
     :class="{'overflow-x-auto': stick === false}"
>
    <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
        <div class="shadow ring-1 ring-black ring-opacity-5 md:rounded" :class="{'overflow-hidden': stick === false}">
            <table {{ $attributes->merge(['class' => 'w-full text-left text-sm text-gray-500 dark:text-gray-400']) }}>
                <thead class="bg-gray-50 text-xs uppercase whitespace-nowrap text-gray-700 dark:bg-gray-700 dark:text-gray-200" :class="{'sticky top-16': stick === true}">
                <tr>
                    {{$head}}
                </tr>
                </thead>
                {{$body}}
            </table>
        </div>
    </div>
</div>

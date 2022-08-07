<div
        x-data
        x-init="new Pikaday({
                        field: $refs.input,
                        format: 'DD.MM.YYYY'
         })"
        @change="$dispatch('input',$event.target.value)"
        class="flex rounded-md shadow-sm"
>{{--"2020-04-13T15:14:22Z";--}}
    <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
            <x-icon.calendar/>
        </span>
    <input
            x-ref="input"
            {{ $attributes->merge(['class' => 'focus:ring-lh-yellow focus:border-lh-yellow dark:bg-gray-400 dark:focus:border-yellow-50 block shadow-sm sm:text-sm border-gray-300 rounded-r-md transition duration-300 ease-in-out']) }}
            type="text" autocomplete="off">
</div>


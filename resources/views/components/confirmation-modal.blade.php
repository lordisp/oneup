@props([
'id' => null,
'maxWidth' => null,
'type' => null,
'form'=>false,
])
<x-modal :id="$id" :maxWidth="$maxWidth" {{ $attributes }}>

            <div class="bg-white dark:bg-gray-800 px-0 pt-5 pb-4 sm:p-6 sm:pb-4">
                {{--<div class="sm:flex sm:items-start">--}}
                @if($type)
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full {{$type =='info' ? 'bg-blue-100' : 'bg-red-100'}} sm:mx-0 sm:h-10 sm:w-10">
                        @if($type=='warning')
                            <x-icon.warning size="6" class="text-red-600"/>
                        @elseif($type=='info')
                            <x-icon.info size="6" class="text-lh-blue"/>
                        @endif
                    </div>
                @endif
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                    <h3>
                        {{ $title }}
                    </h3>

                    <div class="mt-2">
                        {{ $content }}
                    </div>
                </div>
                {{--</div>--}}
            </div>

            <div class="px-6 py-4 bg-gray-100 text-right">
                {{ $footer }}
            </div>

</x-modal>
@props([
'text' => 'text-sm'
])
<div {{$attributes->merge(['class'=>'relative'])}}>
    <div class="absolute inset-0 flex items-center " aria-hidden="true">
        <div class="w-full border-t border-gray-300"></div>
    </div>
    <div class="relative flex justify-start">
    <span class="pr-2 bg-white {{$text ?:'text-sm'}} text-gray-500">
      {{$slot}}
    </span>
    </div>
</div>

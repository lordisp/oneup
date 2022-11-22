@props([
    'heading' => false
])
<div>
    @if($heading)
    <h2 class="text-gray-500 text-xs font-medium uppercase tracking-wide">{{$heading}}</h2>
    @endif
    <ul class="mt-3 grid grid-cols-1 gap-5 sm:gap-6 sm:grid-cols-2 lg:grid-cols-3">
        {{$slot}}
    </ul>
</div>

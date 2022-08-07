@props(['action'])
<div class="{{--border-b border-lhg-gray-12 dark:border-gray-700--}} flex flex-col md:flex-row md:items-center md:justify-between">
    <h2>{{ $slot ?? app()->accessor::title() }}</h2>
    @if(isset($action))
    <div class=" mt-3 flex mt-3 md:mt-0 md:ml-4 space-x-1.5">
        {{ $action }}
    </div>
    @endif
</div>

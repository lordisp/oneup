<nav {{$attributes->merge(['class' => 'h-full overflow-y-auto'])}}  aria-label="Directory">
    <div class="relative">
        <ul class="relative z-0 divide-y divide-gray-200">
            {{$slot}}
        </ul>
    </div>
</nav>
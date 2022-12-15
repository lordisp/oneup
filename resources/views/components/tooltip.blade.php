<span x-data="{ tooltip: false }" x-on:mouseover="tooltip = true" x-on:mouseleave="tooltip = false" class="cursor-pointer">
  {{$icon}}
  <div x-show="tooltip" class="text-sm text-white absolute bg-gray-900 rounded px-2 transform -translate-y-8 translate-x-8">
    {{$slot}}
  </div>
</span>
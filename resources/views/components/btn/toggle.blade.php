@props([
'label' => false
])

<div class="flex items-center ">
    <button {{$attributes}}  type="button"
            class="flex-shrink-0 group relative rounded-full inline-flex items-center justify-center h-5 w-10 cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lh-yellow"
            x-data="{ on: false }" role="switch" aria-checked="false" :aria-checked="on.toString()" @click="on = !on">
        <span class="sr-only">Use setting</span>
        <span aria-hidden="true" class="pointer-events-none absolute bg-white w-full h-full rounded-md"></span>
        <span aria-hidden="true" class="pointer-events-none absolute h-4 w-9 mx-auto rounded-full transition-colors ease-in-out duration-200 bg-gray-200" x-state:on="Enabled" x-state:off="Not Enabled"
              :class="{ 'bg-lh-yellow': on, 'bg-gray-200': !(on) }"></span>
        <span aria-hidden="true"
              class="pointer-events-none absolute left-0 inline-block h-5 w-5 border border-gray-200 rounded-full bg-white shadow transform ring-0 transition-transform ease-in-out duration-200 translate-x-0"
              x-state:on="Enabled" x-state:off="Not Enabled" :class="{ 'translate-x-5': on, 'translate-x-0': !(on) }"></span>
    </button>
        <span class="ml-3" id="annual-billing-label" @click="on = !on; $refs.switch.focus()">
    @if($label)
      <span class="text-sm font-medium text-gray-900">{{$label}}</span>
    @endif
    </span>
</div>


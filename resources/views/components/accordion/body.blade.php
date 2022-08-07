{{--

# Example:

The `request` property is optional to allow conditionaly behave based on the current path. This can be usefull for navigation menues.

<x-accordion>
    <x-accordion.body id="1" title="Question Dashboard" request="dashboard">
        Content Foo
    </x-accordion.body>
    <x-accordion.body id="2" title="Question Admin" request="admin*">
        Content Bar
    </x-accordion.body>
    <x-accordion.body id="3" title="Question Foo" request="foo">
        Content Bar
    </x-accordion.body>
</x-accordion>

--}}

@props([ 'id' => 1, 'title', 'request'=>'' ])
<div x-data="{
        id: {{ $id }},
        request: {{ $request == ''|| request()->is($request) ? 1 : 0 }},
        get expanded() {
            return this.active ===  this.id
        },
        set expanded(value) {
            this.active = value  ? this.id : null
        },
    }"
     x-init="active={{  $request == ''|| request()->is($request) ? $id : null }}"
     role="region" class="border border-black rounded-md shadow">
    <button
        x-on:click="expanded = !expanded"
        :aria-expanded="expanded"
        {{$attributes->merge(['class'=>'flex items-center justify-between w-full px-6 py-3'])}}
    >
        <span>{{ $title }}</span>
        <span x-show="expanded" aria-hidden="true" class="ml-4" x-cloak><x-icon.chevron-down/></span>
        <span x-show="!expanded" aria-hidden="true" class="ml-4 x-cloak"><x-icon.chevron-up/></span>
    </button>

    <div x-show="expanded" x-collapse>
        <div class="pb-4 px-6" x-cloak>{{ $slot }}</div>
        <br>
    </div>
</div>

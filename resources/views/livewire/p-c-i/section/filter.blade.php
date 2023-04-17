<div class="pb-2">
    <x-card.form x-show="filter" x-collapse buttons >
        <div class="gap-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 ">
            <!-- Filters -->
            <x-input.group inline for="status" label="Status">
                <x-input.select wire:model="filters.status" placeholder="select Status" id="filter-status" class="w-full">
                    <option value="review">Review outstanding</option>
                    <option value="open">Optional Review</option>
                    <option value="extended">Extended</option>
                    <option value="deleted">Decommissioned</option>
                    <option value="">Any</option>
                </x-input.select>
            </x-input.group>

            <x-input.group inline for="searchBs" label="Business-Service">
                <div x-data="{show: false}" class="relative mt-1">
                    <x-input.text
                            type="search"
                            @click="show = true"
                            @click.outside="show = false"
                            @keydown.esc="show = false"
                            wire:model.debounce.500ms="searchBs"
                            class="w-full"
                            for="searchBs"
                    />
                    @if(!empty($this->businessServices))
                        <ul x-show="show" class="absolute z-10 mt-1 w-full max-h-24 overflow-auto rounded-md bg-white dark:bg-gray-400 py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm" role="listbox">
                            @foreach($this->businessServices as $b)

                                <li wire:click.prevent="setBs('{{$b['value']}}')" class="cursor-default px-2 py-1 dark:hover:bg-gray-700">{{$b['value']}}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
{{--                <livewire:components.form.select-multi :multiple="true" :options="$this->businessServices"/>--}}
            </x-input.group>

            @can('serviceNow-firewallRequests-readAll')
                <x-input.group inline for="own" label="Show Own">
                    <x-input.checkbox value="" wire:model="filters.own"/>
                </x-input.group>
            @endcan
            <!-- Buttons -->
            <x-slot name="buttons">
                <x-btn.secondary wire:click="resetFilters">Reset Filters</x-btn.secondary>
                <x-btn.secondary x-on:click="filter = false">Hide Filters</x-btn.secondary>
            </x-slot>
        </div>

    </x-card.form>

</div>
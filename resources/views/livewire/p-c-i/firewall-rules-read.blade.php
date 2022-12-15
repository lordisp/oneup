<div class="content" x-data="{filter: false}">
    <x-title>
        {{__('Firewall Requests')}}
        <x-slot name="action">
            <x-input.search wire:model.debounce.500ms="search" placeholder="{{__('form.search')}}..."/>
            <x-btn.blank x-on:click="filter = !filter">
                <x-icon.filter x-show="filter" solid size="5"/>
                <x-icon.filter x-show="!filter" size="5"/>
            </x-btn.blank>
            <x-input.select class="w-20" wire:model="perPage" id="perPage">
                <option>15</option>
                <option>50</option>
                <option>100</option>
            </x-input.select>
        </x-slot>
    </x-title>

    <div class="flex justify-end space-x-1">
        @can('serviceNow-firewallRequests-deleteAll')
            <x-btn.danger wire:loading.class="disabled" wire:click="deleteAll">
                <x-icon.delete/>
            </x-btn.danger>
        @endcan
        @can('serviceNow-firewallRequests-import')
            <x-btn.secondary wire:loading.class="disabled" wire:click="sendNotification">
                Send Notifications
            </x-btn.secondary>
        @endcan
    </div>


    <!-- Filters -->
    <div class="pb-2">
        <x-card.form x-show="filter" x-collapse contrast buttons>
            <div class="gap-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 ">
                <!-- Filters -->
                <x-input.group inline for="pci" label="PCI">
                    <x-input.select wire:model="filters.pci_dss" placeholder="select Type" id="filter-pci" class="w-full">
                        <option value="1">PCI Only</option>
                        <option value="'0'">Non-PCI</option>
                        <option value="">Any</option>
                    </x-input.select>
                </x-input.group>

                <x-input.group inline for="status" label="Status">
                    <x-input.select wire:model="filters.status" placeholder="select Type" id="filter-status" class="w-full">
                        <option value="review">Review outstanding</option>
                        <option value="open">Optional Review</option>
                        <option value="extended">Extended</option>
                        <option value="deleted">Decommissioned</option>
                        <option value="">Any</option>
                    </x-input.select>
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


    <div x-data="{ selectPagePopup:@entangle('selectPagePopup') }">
        <x-table class="md:table-auto md:max-w-screen-2xl">
            <x-slot name="head">
                {{--<x-table.heading class="w-5">
                    <x-input.checkbox wire:model="selectPage" disabled="{{$rows->total() === 0}}"/>
                </x-table.heading>--}}

                <x-table.heading class="hidden md:table-cell w-5"><span class="sr-only">Description</span></x-table.heading>

                <x-table.heading class="hidden md:table-cell">Status</x-table.heading>
            </x-slot>
            <x-slot name="body">
                <x-table.body>
                    {{--<x-table.row>
                        <x-table.cell x-show="selectPagePopup"
                                      class="bg-lhg-yellow text-white font-bold top-0"
                                      colspan="3"
                        >
                            <div class="flex space-x-1">
                                    <span class="whitespace-normal">
                                        {{ __('messages.selected', ['attribute' => count($selected),'type' => 'Rules']) }}
                                        <x-btn.link type="button" wire:click="selectAll">{{ __('messages.select_all', ['attribute' => $rows->total()]) }}</x-btn.link>
                                    </span>
                            </div>
                        </x-table.cell>
                    </x-table.row>--}}
                    @forelse($rows as $row)
                        <x-table.row wire:key="{{$row->id}}" class="hover:bg-gray-50 dark:hover:bg-gray-500 dark:bg-amber-50">
                            {{--<x-table.cell>
                                <x-input.checkbox value="{{ $row->id }}" x-model="$wire.selected" class="'{{$selectPage?'checked':''}}'"/>
                            </x-table.cell>--}}

                            <x-table.cell colspan="2">
                                <div wire:click.prevent="edit('{{ $row->id }}')" class="flex items-center  cursor-pointer">
                                    <div class="flex min-w-0 flex-1 items-center">
                                        <div class="min-w-0 flex-1 px-4 md:grid md:grid-cols-2 md:gap-4">
                                            <div class="grid grid-cols-1 lg:grid-cols-2">
                                                <div class="space-y-2">
                                                    <spam class="truncate text-sm font-medium text-lhg-blue">{{Str::title($row->request->requestor_name)}}</spam>
                                                    <span class=" flex items-center text-sm text-gray-500 space-x-1">
                                                        <x-icon.mail-open mini class="text-gray-400"/>
                                                        <span class="truncate">{{$row->request->requestor_mail}}</span>
                                                    </SPAN>
                                                    <span class="flex items-center text-sm text-gray-500 space-x-1">
                                                        @if($row->newStatus==='review')
                                                            <x-icon.info :class="$row->status_text"/>
                                                        @elseif($row->newStatus==='deleted')
                                                            <x-icon.ban :class="$row->status_text"/>
                                                        @elseif($row->newStatus==='extended')
                                                            <x-icon.check-circle :class="$row->status_text"/>
                                                        @endif
                                                        <span>{{$row->status_name}}</span>
                                                    </span>
                                                    <span class="flex items-center text-xs italic text-gray-500 space-x-1">
                                                        @if(isset($row->lastStatusName) && isset($row->last_review))
                                                            <span class="truncate">{{$row->lastStatusName}} {{$row->last_review->diffForHumans()}}</span>
                                                        @else
                                                            <span class="truncate">Never reviewed</span>
                                                        @endif
                                                    </span>

                                                </div>
                                                <div class="hidden lg:block">
                                                    <ul>
                                                        @foreach($row->request->tags()->get() as $tag)
                                                            @if($tag->value)
                                                                <li>
                                                                    <x-badge class="bg-lhg-yellow text-yellow-900">{{$tag->value}}</x-badge>
                                                                </li>
                                                            @endif
                                                        @endforeach

                                                    </ul>
                                                </div>
                                            </div>

                                            <div class="hidden md:block">
                                                <div class="space-y-2">
                                                    <span class="text-sm text-gray-900">
                                                        {{\Illuminate\Support\Str::replace('Request_Firewall_','',$row->request->subject)}} <span class="italic">({{$row->request->ritm_number}})</span>
                                                    </span>
                                                    <span class="flex items-center text-sm text-gray-500 space-x-1">

                                                        {{ $row->description }}
                                                    </span>
                                                    <span class="flex items-center text-sm text-gray-500 space-x-1">

                                                        {{$row->request->description}}
                                                    </span>
                                                    <span class="flex items-center text-xs italic text-gray-500 space-x-1">

                                                        {{$row->id}}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <x-icon.chevron-right mini class="text-gray-400"/>
                                    </div>
                                </div>
                            </x-table.cell>

                            {{--                            <x-table.cell class="hidden md:table-cell truncate">--}}
                            {{--                                <x-badge class="shadow {{ $row->status_background }} {{ $row->status_text }}">{{ $row->status_name }}</x-badge>--}}
                            {{--                            </x-table.cell>--}}

                        </x-table.row>

                    @empty
                        <x-table.row class="hover:bg-white">
                            <x-table.cell class="md:hidden">
                                    <span class="w-full border text-green-900 border-green-200 border bg-green-50 rounded-md flex items-center justify-center space-x-2 py-4">
                                        <span class="text-lg">{{__('empty-table.nothing_to_do')}}</span>
                                        <span class="-rotate-6"><x-icon.hand-thumb-up/></span>
                                    </span>
                            </x-table.cell>
                            <x-table.cell class="hidden md:table-cell" colspan="3">
                                    <span class="w-full border text-green-900 border-green-200 border bg-green-50 rounded-md flex items-center justify-center space-x-2 py-4">
                                        <span class="text-lg">{{__('empty-table.nothing_to_do')}}</span>
                                        <span class="-rotate-6"><x-icon.hand-thumb-up/></span>
                                    </span>
                            </x-table.cell>
                        </x-table.row>
                    @endforelse
                </x-table.body>
            </x-slot>
        </x-table>
    </div>
    <!-- Section Pagination -->
    <div class="text-xs flex justify-center items-center md:justify-start md:items-start">
        Showing {{{ $rows->count() ." of ". $rows->total() }}}</div>
    <div>{{ $rows->onEachSide(2)->links('components/paginate') }}</div>

    <!-- Section edit-->

    <x-modal modal="edit">
        <x-modal.panel class="md:max-w-6xl space-y-4">

            <x-slot name="title">
                @if(isset($rule)&& !empty($rule))
                    <div>
                        <span>{{Str::replace('Request_Firewall_',' ',$rule->request->subject)}} </span>
                        <span class="italic font-normal text-sm">({{$rule->request->ritm_number}})</span>
                        <span class="italic font-normal text-sm">({{$rule->id}})</span>
                    </div>

                @endif

            </x-slot>
            <x-slot name="content">
                @if(isset($rule)&& !empty($rule))

                    <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                        <dl class="sm:divide-y sm:divide-gray-200">
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Last Review</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{isset($rule->last_review)?$rule->last_review->diffForHumans():'Never'}}</dd>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Description</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{$rule->description}}</dd>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Business Purpose</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{$rule->business_purpose}}</dd>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Source</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{$rule->source}}</dd>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Service</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{$rule->service}}</dd>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Destination</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{$rule->destination}}</dd>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Destination Port(s)</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{$rule->destination_port}}</dd>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">NAT Required</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{$rule->nat_required}}</dd>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">PCI Relevant</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{$rule->pci_dss}}</dd>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Application Id</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{$rule->application_id}}</dd>
                            </div>

                        </dl>
                    </div>

                @endif

            </x-slot>

            <x-slot name="button">

                @if(isset($rule->newStatus)&&$rule->status!='deleted')
                    @if($rule->newStatus!='extended')
                        <button
                                wire:loading.attr="disabled"
                                wire:loading.class="cursor-progress"
                                wire:click="extendConfirm" class="btn-green" type="button">Extend
                        </button>
                    @endif
                    <x-btn.danger
                            wire:loading.attr="disabled"
                            wire:loading.class="cursor-progress"
                            wire:click="deleteConfirm" type="button">Decommission
                    </x-btn.danger>
                @endif
                <x-btn.secondary x-on:click="open=false">Cancel</x-btn.secondary>
            </x-slot>

        </x-modal.panel>
    </x-modal>

    <!-- Section Confirmation Modal "extend" -->

    <x-modal modal="extendConfirm">
        <x-modal.panel class="md:max-w-lg space-y-4" type="info" title="{{ __('lines.are_you_sure') }}">
            <x-slot name="content">
                @if(isset($rule))
                    <div class="text-xs md:text-base space-y-2">
                        {{ __('lines.rule.extend', ['request' => $rule->request->ritm_number, 'date' => now()->addMonths(7)->diffForHumans()]) }}
                    </div
                            @endif>
            </x-slot>
            <x-slot name="button">
                <x-btn.secondary wire:click="extend">Confirm Extension</x-btn.secondary>
                <x-btn.primary x-on:click="open=false">Cancel</x-btn.primary>
            </x-slot>
        </x-modal.panel>
    </x-modal>
    <!-- Section Confirmation Modal "decom" -->

    <x-modal modal="deleteConfirm">
        <x-modal.panel class="md:max-w-lg space-y-4" type="warning" title="{{ __('lines.are_you_sure') }}">
            <x-slot name="content">
                @if(isset($rule))
                    <div class="text-xs md:text-base space-y-2">
                        {{__('modal.firewall_decommission', ['attribute' => $rule->request->ritm_number ])}}
                    </div
                            @endif>
            </x-slot>
            <x-slot name="button">
                <x-btn.danger wire:click="delete">Confirm Decommission</x-btn.danger>
                <x-btn.primary x-on:click="open=false">Cancel</x-btn.primary>
            </x-slot>
        </x-modal.panel>
    </x-modal>
</div>

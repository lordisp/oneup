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
        <x-card.form x-show="filter" x-collapse buttons>
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
        <x-table class="md:table-auto">
            <x-slot name="head">
                <x-table.heading class="hidden md:table-cell w-5"><span>Rule</span></x-table.heading>
                <x-table.heading class="hidden md:table-cell">Description</x-table.heading>
                <x-table.heading class="hidden md:table-cell">Status</x-table.heading>
            </x-slot>
            <x-slot name="body">
                <x-table.body>
                    @forelse($rows as $row)
                        <x-table.row wire:click.prevent="edit('{{ $row->id }}')" wire:key="{{$row->id}}">
                            <x-table.cell>
                                <div class="flex items-center  cursor-pointer">
                                    <div class="flex flex-1 items-center">
                                        <div class="grid grid-cols-1 md:grid-cols-none">
                                            <div class="space-y-2">
                                                <div class="truncate md:flex md:flex-col">
                                                    <spam class="truncate text-sm font-bold text-lhg-blue dark:text-white">
                                                        {{$row->request->subjectName}}
                                                    </spam>
                                                    <spam class="truncate text-xs italic text-lhg-blue dark:text-white">
                                                        ({{$row->business_service}})
                                                    </spam>
                                                </div>
                                                <span class=" md:hidden flex items-center text-sm space-x-1">
                                                    <span class="truncate">{{$row->description}}</span>
                                                </span>
                                                <span class="md:hidden flex items-center text-sm space-x-1">
                                                        @if($row->newStatus==='review')
                                                        <x-icon.info :class="$row->status_text"/>
                                                    @elseif($row->newStatus==='deleted')
                                                        <x-icon.ban :class="$row->status_text"/>
                                                    @elseif($row->newStatus==='extended')
                                                        <x-icon.check-circle :class="$row->status_text"/>
                                                    @endif
                                                        <span>{{$row->status_name}}</span>
                                                    </span>
                                                <span class="md:hidden flex items-center text-xs italic space-x-1">
                                                @if(isset($row->lastStatusName) && isset($row->last_review))
                                                        <span class="truncate">{{$row->lastStatusName}} {{$row->last_review->diffForHumans()}}</span>
                                                    @else
                                                        <span class="truncate">Never reviewed</span>
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="md:hidden">
                                        <x-icon.chevron-right mini class="text-gray-400"/>
                                    </div>
                                </div>
                            </x-table.cell>

                            <x-table.cell class="hidden md:table-cell">
                                <div class="whitespace-normal">
                                    <div><span class="font-bold mr-1">Source</span>{{\Illuminate\Support\Str::limit($row->source,80)}}</div>
                                    <div><span class="font-bold mr-1">Destination</span>{{\Illuminate\Support\Str::limit($row->destination,80)}}</div>
                                    <div>{{$row->description}}</div>
                                </div>
                            </x-table.cell>
                            <x-table.cell class="hidden md:table-cell">
                                <div class="flex justify-between">
                                    <div>
                                        <span class="flex items-center text-sm space-x-1">
                                        @if($row->newStatus==='review')
                                                <x-icon.info :class="$row->status_text"/>
                                            @elseif($row->newStatus==='deleted')
                                                <x-icon.ban :class="$row->status_text"/>
                                            @elseif($row->newStatus==='extended')
                                                <x-icon.check-circle :class="$row->status_text"/>
                                            @endif
                                        <span>{{$row->status_name}}</span>
                                    </span>
                                        <span class="flex items-center text-xs italic space-x-1">
                                    @if(isset($row->lastStatusName) && isset($row->last_review))
                                                <span class="truncate">{{$row->lastStatusName}} {{$row->last_review->diffForHumans()}}</span>
                                            @else
                                                <span class="truncate">Never reviewed</span>
                                            @endif
                                        </span>
                                    </div>
                                    <div>
                                        <x-icon.chevron-right mini class="text-gray-400"/>
                                    </div>
                                </div>
                            </x-table.cell>
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
                        <span>{{Str::replace('Request_Firewall_',' ',$rule->request->subjectName)}} </span>
                        <span class="italic font-normal text-sm">({{$rule->request->ritm_number}})</span>
                        <span class="italic font-normal text-sm">({{$rule->id}})</span>
                    </div>

                @endif

            </x-slot>
            <x-slot name="content">
                @if(isset($rule)&& !empty($rule))
                    <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-5 sm:p-0">
                        <dl class="sm:divide-y sm:divide-gray-200 dark:sm:divide-gray-700">
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
                                <dt class="text-sm font-medium">Last Review</dt>
                                <dd class="mt-1 text-sm sm:col-span-2 sm:mt-0">{{isset($rule->last_review)?$rule->last_review->diffForHumans():'Never'}}</dd>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
                                <dt class="text-sm font-medium">Description</dt>
                                <dd class="mt-1 text-sm sm:col-span-2 sm:mt-0">{{$rule->description}}</dd>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
                                <dt class="text-sm font-medium">Business Purpose</dt>
                                <dd class="mt-1 text-sm sm:col-span-2 sm:mt-0">{{$rule->business_purpose}}</dd>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
                                <dt class="text-sm font-medium">Source</dt>
                                <dd class="mt-1 text-sm sm:col-span-2 sm:mt-0">{{$rule->source}}</dd>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
                                <dt class="text-sm font-medium">Service</dt>
                                <dd class="mt-1 text-sm sm:col-span-2 sm:mt-0">{{$rule->service}}</dd>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
                                <dt class="text-sm font-medium">Destination</dt>
                                <dd class="mt-1 text-sm sm:col-span-2 sm:mt-0">{{$rule->destination}}</dd>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
                                <dt class="text-sm font-medium">Destination Port(s)</dt>
                                <dd class="mt-1 text-sm sm:col-span-2 sm:mt-0">{{$rule->destination_port}}</dd>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
                                <dt class="text-sm font-medium">NAT Required</dt>
                                <dd class="mt-1 text-sm sm:col-span-2 sm:mt-0">{{$rule->nat_required}}</dd>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
                                <dt class="text-sm font-medium">PCI Relevant</dt>
                                <dd class="mt-1 text-sm sm:col-span-2 sm:mt-0">{{$rule->pci}}</dd>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
                                <dt class="text-sm font-medium">Application Id</dt>
                                <dd class="mt-1 text-sm sm:col-span-2 sm:mt-0">{{$rule->application_id}}</dd>
                            </div>
                        </dl>
                    </div>

                @endif

            </x-slot>

            <x-slot name="button">
                <div class="space-y-1 md:space-x-1">
                    @if(isset($rule->newStatus)&&$rule->status!='deleted')
                        @if($rule->newStatus!='extended')
                            <x-btn.secondary
                                    wire:loading.attr="disabled"
                                    wire:loading.class="cursor-progress"
                                    wire:click="extendConfirm" type="button">Extend
                            </x-btn.secondary>
                        @endif
                        <x-btn.danger
                                wire:loading.attr="disabled"
                                wire:loading.class="cursor-progress"
                                wire:click="deleteConfirm" type="button">Decommission
                        </x-btn.danger>
                    @endif
                    <x-btn.primary x-on:click="open=false">Cancel</x-btn.primary>
                </div>
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

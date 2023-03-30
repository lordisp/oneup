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
            <x-btn.danger wire:loading.class="disabled" wire:click="deleteAll" wire:target="deleteAll">
                <x-icon.delete wire:loading.class="hidden" wire:target="deleteAll"/>
                <x-icon.spinner class="hidden" wire:loading.class.remove="hidden" wire:target="deleteAll"/>
            </x-btn.danger>
        @endcan
        @can('serviceNow-firewallRequests-import')
            <x-btn.secondary wire:loading.class="disabled" wire:click="sendNotification" wire:target="sendNotification">
                Send Notifications
            </x-btn.secondary>
        @endcan
    </div>


    <!-- Filters -->
    @include('livewire.p-c-i.section.filter')


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
                        <x-table.row wire:click.prevent="edit('{{ $row->id }}')" wire:key="{{$row->id}}" class=cursor-pointer>
                            <x-table.cell>
                                <div class="flex items-center  ">
                                    <div class="flex flex-1 items-center">
                                        <div class="grid grid-cols-1 md:grid-cols-none">
                                            <div class="space-y-2">
                                                <div class="truncate md:flex md:flex-col">
                                                    <spam class="truncate text-sm font-bold text-lhg-blue dark:text-white">
                                                        {{$row->request->ritm_number}}
                                                    </spam>
                                                    <spam class="truncate text-sm text-lhg-blue dark:text-white">
                                                        {{$row->request->created_at->format('d.m.Y')}}
                                                    </spam>
                                                    <spam class="truncate text-xs italic text-lhg-blue dark:text-white">
                                                        ({{$row->businessServiceName}})
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
                                                        <span>{{$row->request->created_at->diffForHumans()}}</span>
                                                </span>
                                                <span class="md:hidden flex items-center text-xs italic space-x-1">
                                                @if(isset($row->lastStatusName) && isset($row->last_review))
                                                        <span class="truncate">{{$row->lastStatusName}} {{$row->last_review->diffForHumans()}}</span>
                                                    @else
                                                        <span class="truncate">Never reviewed</span>
                                                    @endif
                                                        <span class="truncate">{{$row->request->created_at->diffForHumans()}}</span>
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
                                    <div><span class="font-bold mr-1">Source</span>{{\Illuminate\Support\Str::limit($row->sourceString,80)}}</div>
                                    <div><span class="font-bold mr-1">Destination</span>{{\Illuminate\Support\Str::limit($row->destinationString,80)}}</div>
                                    <div>{{$row->description}}</div>
                                </div>
                            </x-table.cell>
                            <x-table.cell class="hidden md:table-cell">
                                <div class="flex justify-between">
                                    @include('livewire.p-c-i.table-cell.status')
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
                        <span>{{Str::replace('Request_Firewall_',' ',$rule->request->subject)}} </span>
                        <span class="italic font-normal text-lg ">({{$rule->request->ritm_number}})</span>
                    </div>

                @endif

            </x-slot>
            <x-slot name="content">
                @if(isset($rule)&& !empty($rule))
                    <div class="relative border-t border-gray-200 dark:border-gray-600 px-4 py-5 sm:px-6">
                        {{-- Content --}}
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">

                            <x-dl.dl1 class="sm:col-span-1" title="Status">
                                <span class="flex items-center text-sm space-x-1">
                                    @if($rule->newStatus==='review')
                                        <x-icon.info :class="$rule->status_text"/>
                                    @elseif($rule->newStatus==='deleted')
                                        <x-icon.ban :class="$rule->status_text"/>
                                    @elseif($rule->newStatus==='extended')
                                        <x-icon.check-circle :class="$rule->status_text"/>
                                    @endif
                                    <span>{{$rule->status_name}}</span>
                                </span>
                            </x-dl.dl1>

                            <x-dl.dl1 class="sm:col-span-1" title="Last review">
                                @if(isset($rule->lastStatusName) && isset($rule->last_review))
                                    <span class="truncate">{{$rule->last_review->diffForHumans()}} on {{$rule->last_review->format('d.m.Y')}}</span>
                                @else
                                    <span class="truncate">Never reviewed</span>
                                @endif
                            </x-dl.dl1>
                            <x-dl.dl1 class="sm:col-span-1" title="PCI Scope">
                                {{$rule->pci}}
                            </x-dl.dl1>
                            <x-dl.dl1 class="sm:col-span-1" title="Business Service">
                                {{$rule->businessServiceName}}
                            </x-dl.dl1>
                            <x-dl.dl1 class="sm:col-span-1" title="Source">
                                <ul class="max-h-28 overflow-scroll">
                                    @foreach(json_decode($rule->source,true) as $value)
                                        <li>{{$value}}</li>
                                    @endforeach
                                </ul>
                            </x-dl.dl1>
                            <x-dl.dl1 class="sm:col-span-1" title="Destination">
                                <ul class="max-h-28 overflow-scroll">
                                    @foreach(json_decode($rule->destination,true) as $value)
                                        <li>{{$value}}</li>
                                    @endforeach
                                </ul>
                            </x-dl.dl1>

                            <x-dl.dl1 class="sm:col-span-1" title="Service">
                                {{$rule->service}}
                            </x-dl.dl1>
                            <x-dl.dl1 class="sm:col-span-1" title="Port(s)">
                                <ul class="max-h-28 overflow-scroll">
                                    @foreach(json_decode($rule->destination_port,true) as $value)
                                        <li>{{$value}}</li>
                                    @endforeach
                                </ul>
                            </x-dl.dl1>
                            <x-dl.dl1 class="sm:col-span-1" title="Requested by">
                                <ul class="max-h-28 overflow-scroll flex space-x-1">
                                    <span>{{$rule->request->requestor_name}}</span>
                                    @if($rule->request->requestor_mail && !str_contains($rule->request->requestor_name,'Inactive'))
                                        <span>
                                            <a class="outline-none" target="_blank" href="https://teams.microsoft.com/l/chat/0/0?users={{$rule->request->requestor_mail}}">
                                                <x-icon.mail-close/>
                                            </a>
                                    </span>
                                    @endif
                                </ul>
                            </x-dl.dl1>

                            <x-dl.dl1 class="sm:col-span-1" title="Cost-Center">
                                {{$rule->request->cost_center}}
                            </x-dl.dl1>



                            <x-dl.dl1 class="sm:col-span-1" title="Requested at">
                                {{$rule->request->created_at->format('d.m.Y')}}
                            </x-dl.dl1>
                            <x-dl.dl1 class="sm:col-span-1">
                                <x-slot name="title">
                                  @if($rule->end_date < now())
                                      Expired at
                                  @else
                                      Expires
                                  @endif
                                </x-slot>
                                {{$rule->expires}}
                            </x-dl.dl1>

                            <x-dl.dl1 class="sm:col-span-1" title="Description">
                                {{$rule->request->description}}
                                @if($rule->request->description !==$rule->description)
                                    <div>
                                        {{$rule->description}}
                                    </div>
                                @endif
                            </x-dl.dl1>

                        </dl>
                        <div class="absolute -top-14 -right-2">
                            <button x-on:click="open=false" class="inline-flex justify-center px-1 py-1 dark:text-white text-sm font-medium rounded-md focus:outline-none">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif
            </x-slot>

            <x-slot name="button">
                <div class="space-y-1 md:space-x-1">
                    @can('extend',$rule)
                        <x-btn.primary
                                wire:loading.attr="disabled"
                                wire:loading.class="cursor-progress"
                                wire:target="extendConfirm"
                                wire:click="extendConfirm" type="button">Extend
                        </x-btn.primary>
                    @endcan
                    @can('decommission',$rule)
                        <x-btn.danger
                                wire:loading.attr="disabled"
                                wire:loading.class="cursor-progress"
                                wire:target="deleteConfirm"
                                wire:click="deleteConfirm" type="button">Decommission
                        </x-btn.danger>
                    @endcan
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
                        {{ __('lines.rule.extend', ['request' => $rule->request->ritm_number, 'date' => now()->addQuarter()->format('d.m.Y')]) }}
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

<x-modal modal="edit">
    <x-modal.panel class="md:max-w-6xl space-y-4 ">

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
                    <x-tabs default="connections">
                        <div class="sm:flex">
                            <x-tabs.mobile>
                                <option value="connections">Connections</option>
                                <option value="commercials">Commercials</option>
                                <option value="history">History</option>
                            </x-tabs.mobile>

                            <x-tabs.list>Connections</x-tabs.list>
                            <x-tabs.list>Commercials</x-tabs.list>
                            <x-tabs.list>History</x-tabs.list>
                        </div>

                        <x-tabs.panel name="Connections">
                            <dl class="mt-3 sm:mt-0 p-3 bg-gray-100 dark:bg-gray-900 rounded grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
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
                                <x-dl.dl1 class="sm:col-span-2" title="Description">
                                    {{$rule->request->description}}
                                    @if($rule->request->description !==$rule->description)
                                        <div>
                                            {{$rule->description}}
                                        </div>
                                    @endif
                                </x-dl.dl1>
                            </dl>
                        </x-tabs.panel>
                        <x-tabs.panel name="Commercials">
                            <dl class="mt-3 sm:mt-0 p-3 bg-gray-100 dark:bg-gray-900 rounded grid grid-cols-1 sm:grid-cols-none gap-4 sm:grid-rows-3 sm:grid-flow-col">
                                <x-dl.dl1 class="sm:col-span-2" title="PCI Scope">
                                    {{$rule->pci}}
                                </x-dl.dl1>
                                <x-dl.dl1 class="sm:col-span-2" title="Business Service">
                                    {{$rule->businessServiceName}}
                                </x-dl.dl1>
                                <x-dl.dl1 class="sm:col-span-2" title="Requested by">
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
                                <x-dl.dl1 class="sm:col-span-2" title="Cost-Center">
                                    {{$rule->request->cost_center}}
                                </x-dl.dl1>
                                <x-dl.dl1 class="sm:col-span-2" title="Requested at">
                                    {{$rule->request->created_at->format('d.m.Y')}}
                                </x-dl.dl1>
                                <x-dl.dl1 class="sm:col-span-2">
                                    <x-slot name="title">
                                        @if($rule->end_date < now())
                                            Expired at
                                        @else
                                            Expires
                                        @endif
                                    </x-slot>
                                    {{$rule->expires}}
                                </x-dl.dl1>
                                <x-dl.dl1 class="sm:row-span-3 overflow-y-scroll" title="Co-Workers">
                                    <ul>
                                        @foreach($rule->businessService->users as $user)
                                            <li>
                                                <a class="flex space-x-1 hover:text-lhg-yellow outline-none" target="_blank"
                                                   href="https://teams.microsoft.com/l/chat/0/0?users={{$user->email}}">
                                                    <x-icon.mail-close/>
                                                    <span>
                                                            {{$user->displayName}}
                                                        </span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </x-dl.dl1>
                            </dl>
                        </x-tabs.panel>
                        <x-tabs.panel name="History">
                            <div class="mt-2 sm:mt-0 p-2 sm:bg-gray-100 sm:dark:bg-gray-900 rounded overflow-hidden -mx-8 lg:-mx-8">
                                <x-table sticky="false" tr-class="border-b border-x-gray-400 border-gray-200 dark:border-gray-500">
                                    <x-slot:head>
                                        <x-table.heading class="border-l-2 sm:border-0">Initiated by</x-table.heading>
                                        <x-table.heading class="hidden sm:table-cell">Activity</x-table.heading>
                                        <x-table.heading class="hidden md:table-cell">Status</x-table.heading>
                                        <x-table.heading class="hidden sm:table-cell">Date</x-table.heading>
                                    </x-slot:head>
                                    <x-slot:body>
                                        <x-table.body>
                                            @forelse($rule->audits as $audit)
                                                <x-table.row>
                                                    <x-table.cell class="border-l-2 sm:border-0 {{$audit->statusBorder}}">
                                                        <div class="flex justify-between font-bold sm:font-normal overflow-hidden">
                                                            <div class="flex-shrink-1 truncate">{{$audit->actor}}</div>
                                                            <div class="sm:hidden truncate">{{$audit->created_at->format('d.m.Y')}}</div>
                                                        </div>
                                                        <dl class="sm:hidden">
                                                            <dt></dt>
                                                            <dd>{{$audit->activity}}</dd>
                                                        </dl>
                                                    </x-table.cell>
                                                    <x-table.cell class="hidden sm:table-cell"> {{$audit->activity}}</x-table.cell>
                                                    <x-table.cell class="hidden md:table-cell">
                                                        <x-badge class="{{$audit->statusBackground}} {{$audit->statusBorder}} {{$audit->statustext}}">{{$audit->status}}</x-badge>
                                                    </x-table.cell>
                                                    <x-table.cell class="hidden sm:table-cell"> {{$audit->created_at->format('d.m.Y')}}</x-table.cell>
                                                </x-table.row>
                                            @empty
                                                <x-table.row>
                                                    <x-table.cell>No records found!</x-table.cell>
                                                </x-table.row>
                                            @endforelse
                                        </x-table.body>
                                    </x-slot:body>
                                </x-table>
                            </div>
                        </x-tabs.panel>
                    </x-tabs>

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
                @can('decommission',$rule)
                    @if($rule->status !== 'deleted')
                        <x-btn.danger
                                wire:loading.attr="disabled"
                                wire:loading.class="cursor-progress"
                                wire:target="deleteConfirm"
                                wire:click="deleteConfirm" type="button">Decommission
                        </x-btn.danger>
                    @endif
                @endcan
                @can('extend',$rule)
                    <x-btn.primary
                            wire:loading.attr="disabled"
                            wire:loading.class="cursor-progress"
                            wire:target="extendConfirm"
                            wire:click="extendConfirm" type="button">Extend
                    </x-btn.primary>
                @endcan
            </div>
        </x-slot>

    </x-modal.panel>
</x-modal>

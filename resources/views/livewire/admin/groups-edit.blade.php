<div class="content">
    <x-title>
        {{ __('Edit Group') }}
    </x-title>

    <x-card.gray class="rounded-lg">
        <x-card.fields>
            <x-slot name="title">
                Overview
            </x-slot>

            <x-slot name="subtitle">
                {{$edit->name}}
            </x-slot>
            <x-card.form>
                <div>
                    <x-grid-list.list>
                        <x-grid-list.simple-card href="{{route('admin.group.edit',[$edit->id,'members'])}}"
                                                 colour="bg-lhg-yellow">
                            <x-slot name="int"> {{$edit->users()->count()}}</x-slot>
                            @if($edit->users()->count()>1)
                                {{Str::plural('Member')}}
                            @else
                                {{Str::singular('Member')}}
                            @endif
                        </x-grid-list.simple-card>
                        <x-grid-list.simple-card href="{{route('admin.group.edit',[$edit->id,'owners'])}}"
                                                 colour="bg-lhg-yellow">
                            <x-slot name="int">{{$edit->owners()->count()}}</x-slot>
                            @if($edit->owners()->count()>1)
                                {{Str::plural('Owner')}}
                            @else
                                {{Str::singular('Owner')}}
                            @endif
                        </x-grid-list.simple-card>
                        <x-grid-list.simple-card href="{{route('admin.group.edit',[$edit->id,'roles'])}}"
                                                 colour="bg-lhg-yellow">
                            <x-slot name="int">{{$edit->roles()->count()}}</x-slot>
                            @if($edit->roles()->count()>1)
                                {{Str::plural('Role')}}
                            @else
                                {{Str::singular('Role')}}
                            @endif
                        </x-grid-list.simple-card>
                    </x-grid-list.list>
                </div>
            </x-card.form>
        </x-card.fields>
        <!-- Section Allocations -->
        @if(!empty($tab)&&$tab!='overview')
            <x-card.fields>
                <x-slot name="title">{{Str::ucfirst($tab)}}</x-slot>
                <x-slot name="subtitle">Assign or remove {{Str::ucfirst($tab)}} to
                    <strong>{{$edit->name}}</strong> Group
                </x-slot>
                <x-card.form buttons search paddingless>
                    <div class="flex-1 min-w-0 space-y-2 sm:px-4">
                        <!-- Section Main -->
                        <div x-data="{ selectPagePopup:@entangle('selectPagePopup') }">
                            <x-table class="md:table-auto md:max-w-screen-2xl">
                                <x-slot name="head">
                                    <x-table.heading>
                                        <x-input.checkbox wire:model="selectPage" disabled="{{$rows->total() === 0}}"/>
                                    </x-table.heading>

                                    <x-table.heading sortable multiColumn wire:click="sortBy('name')"
                                                     :direction="$sorts['name'] ?? null">
                                        Name
                                    </x-table.heading>

                                    @if($tab=='roles')
                                        <x-table.heading class="hidden md:table-cell" sortable multiColumn
                                                         wire:click="sortBy('description')"
                                                         :direction="$sorts['description'] ?? null">Description
                                        </x-table.heading>

                                    @else
                                        <x-table.heading class="hidden md:table-cell" sortable multiColumn
                                                         wire:click="sortBy('email')"
                                                         :direction="$sorts['email'] ?? null">Email
                                        </x-table.heading>
                                    @endif

                                    <x-table.heading class="hidden md:table-cell"><span class="sr-only">Options</span>
                                    </x-table.heading>

                                </x-slot>
                                <x-slot name="body">
                                    <x-table.body>
                                        <x-table.row>
                                            <x-table.cell x-show="selectPagePopup"
                                                          class="bg-lhg-yellow text-white font-bold top-0"
                                                          colspan="5">
                                                <div class="flex space-x-1">
                                <span class="whitespace-normal">
                                    {{ __('messages.selected', ['attribute' => count($selected),'type' => 'roles']) }}
                                <x-btn.link type="button"
                                            wire:click="selectAll">{{ __('messages.select_all', ['attribute' => $rows->total()]) }}</x-btn.link>
                                </span>
                                                </div>
                                            </x-table.cell>
                                        </x-table.row>
                                        @forelse($rows as $row)
                                            <x-table.row wire:key="{{$row->id}}">
                                                <x-table.cell>
                                                    <x-input.checkbox value="{{ $row->id }}" x-model="$wire.selected"
                                                                      class="'{{$selectPage?'checked':''}}'"/>
                                                </x-table.cell>

                                                <x-table.cell>
                                                    <span class="hidden md:block">{{ $row->firstName }} {{ $row->lastName }} {{ $row->name }}</span>
                                                    <div wire:click.prevent="edit('{{$row->id}}')"
                                                         class="md:hidden grid grid-cols-1 cursor-pointer">
                                                        <div class="col-span-1 -mt-1.5 truncate space-y-1">
                                                            <div class="md:hidden truncate font-bold">{{ $row->firstName }} {{ $row->lastName }} {{ $row->name }}</div>
                                                            <div class="truncate">{{ $row->email }} {{ $row->description }}</div>
                                                        </div>
                                                    </div>
                                                </x-table.cell>

                                                <x-table.cell
                                                        class="hidden md:table-cell truncate">{{ $row->email }} {{ $row->description }}</x-table.cell>
                                                <x-table.cell class="hidden md:table-cell text-right">
                                                    <a href="{{route('admin.roles.edit', ['id' => $row->id])}}">
                                                        <x-icon.edit/>
                                                    </a>

                                                </x-table.cell>
                                            </x-table.row>
                                        @empty
                                            <x-table.row>
                                                <x-table.cell class="md:hidden">
                            <span class="w-full flex items-center justify-center space-x-2 py-4"><x-icon.emoji-sad
                                        size="6"/><span
                                        class="text-lg">{{__('empty-table.admin_provider',['attribute' => 'role'])}}</span></span>
                                                </x-table.cell>
                                                <x-table.cell class="hidden md:table-cell" colspan="5">
                            <span class="w-full flex items-center justify-center space-x-2 py-4"><x-icon.emoji-sad
                                        size="6"/><span
                                        class="text-lg">{{__('empty-table.admin_provider',['attribute' => 'roles'])}}</span></span>
                                                </x-table.cell>
                                            </x-table.row>
                                        @endforelse
                                    </x-table.body>
                                </x-slot>
                            </x-table>
                        </div>

                        <!-- Section Confirmation Modal "Remove" -->
                        <x-modal modal="delete" wire:model.defer="showDeleteModal">
                            <x-modal.panel class="md:max-w-lg space-y-4" type="warning" title="Are you sure?">
                                <x-slot name="content">
                                    <div class="text-xs md:text-base space-y-2">
                                        @choice('modal.delete',count($selected), ['count' => count($selected),'attribute' => Str::singular(Str::ucfirst($tab))])
                                    </div>
                                </x-slot>
                                <x-slot name="button">
                                    <x-btn.danger wire:click="detach">Remove</x-btn.danger>
                                    <x-btn.secondary x-on:click="open=false">Cancel</x-btn.secondary>
                                </x-slot>
                            </x-modal.panel>
                        </x-modal>

                        <!-- Section Slide-Over "Add Allocations" -->
                        <div>
                            <form wire:submit.prevent="save">
                                <x-slide-over class="max-h-screen  overflow-hidden">
                                    <x-slot name="title">Add {{Str::ucfirst($tab)}}</x-slot>
                                    <x-slot name="content">
                                        <x-input.text type="search" class="w-full"
                                                      wire:model="search"></x-input.text>
                                        <div class="h-1/2 overflow-y-auto py-1">
                                            @if(isset($results) && !empty($results) )
                                                <x-list>
                                                    @forelse($results as $result)
                                                        <x-list.sticky
                                                                wire:click.prevent="add({{json_encode($result)}})"
                                                                size="2" withImage="true" secondline>
                                                            <x-slot name="image">
                                                                <img class="h-10 w-10 rounded-full"
                                                                     src="{{isset($result['avatar']) ? $result['avatar'] : 'https://ui-avatars.com/api/?name='.urlencode($result['name']).'&color=7F9CF5&background=random'}}"
                                                                     alt="{{$result['name']}}">
                                                            </x-slot>
                                                            <x-slot name="first">
                                                                {{$result->name}} {{$result->firstName}} {{$result->lastName}}
                                                            </x-slot>
                                                            <x-slot name="second">
                                                                {{strtolower($tab != 'roles' ? $result['email'] : $result['description'])}}
                                                            </x-slot>
                                                        </x-list.sticky>
                                                    @empty
                                                        <div class="flex italic justify-center pt-2 text-sm">No results
                                                            found ...
                                                        </div>
                                                    @endforelse
                                                </x-list>
                                            @endif
                                        </div>
                                        <x-divider class="py-4">Selected</x-divider>
                                        <div class="h-1/2 overflow-y-auto py-1">
                                            @if(!empty($selectedResults))
                                                <x-list>
                                                    @forelse($selectedResults as $selectedResult)
                                                        <x-list.sticky
                                                                wire:click.prevent="remove({{json_encode($selectedResult)}})"
                                                                size="2" withImage="true" secondline>
                                                            <x-slot name="image">
                                                                <img class="h-10 w-10 rounded-full"
                                                                     src="{{isset($selectedResult['avatar']) ? $selectedResult['avatar'] : 'https://ui-avatars.com/api/?name='.urlencode(array_key_exists('displayName',$selectedResult)?$selectedResult['displayName']:$selectedResult['name']).'&color=7F9CF5&background=random'}}"
                                                                     alt="{{array_key_exists('displayName',$selectedResult)?$selectedResult['displayName']:$selectedResult['name']}}">
                                                            </x-slot>
                                                            <x-slot name="first">
                                                                {{strtolower($tab != 'roles' ? $selectedResult['firstName'] .' '.$selectedResult['lastName']: $selectedResult['name'])}}
                                                            </x-slot>
                                                            <x-slot name="second">
                                                                {{strtolower($tab != 'roles' ? $selectedResult['email'] : $selectedResult['description'])}}
                                                            </x-slot>
                                                        </x-list.sticky>
                                                    @empty
                                                        <div class="flex italic justify-center pt-2 text-sm">No results
                                                            found ...
                                                        </div>
                                                    @endforelse
                                                </x-list>
                                            @endif
                                        </div>
                                    </x-slot>
                                </x-slide-over>
                            </form>
                        </div>
                        <x-slot name="buttons">
                            @canany(['group-detach-members','group-attach-members'],$edit)
                                <x-btn.primary active="{{$active}}" wire:click="detachModal">
                                    Remove {{Str::ucfirst($tab)}}
                                </x-btn.primary>
                                <x-btn.primary active="{!! Gate::allows('group-attach-members', $this->edit)!!}" @click.prevent="open = true">
                                    Add {{Str::ucfirst($tab)}}
                                </x-btn.primary>
                            @endcanany
                        </x-slot>
                    </div>
                </x-card.form>
            </x-card.fields>
        @endif
    </x-card.gray>
</div>
<!-- Section -->


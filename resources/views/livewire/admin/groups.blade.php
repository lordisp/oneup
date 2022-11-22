<div class="content">
    <x-title>
        {{ __('Groups') }}
        <x-slot name="action">
            <x-input.search wire:model.debounce.500ms="search" placeholder="{{__('form.search')}}..."/>
            <x-input.select class="w-20" wire:model="perPage" id="perPage">
                <option>15</option>
                <option>50</option>
                <option>100</option>
            </x-input.select>
        </x-slot>
    </x-title>

    <div class="flex justify-end space-x-1">
        @can('group-delete')
            <x-btn.danger wire:click="deleteModal"
                          class="{{empty($selected) ? 'hidden' : ''}}">{{ __('button.delete', ['attribute' => 'Group']) }}</x-btn.danger>
        @endcan
        @can('group-create',auth()->user())
            <a class="btn-secondary"
               href="{{route('admin.group.create')}}">{{ __('button.create_new', ['attribute' => 'Group']) }}</a>
        @endcan
    </div>

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

                <x-table.heading class="hidden md:table-cell" sortable multiColumn wire:click="sortBy('description')"
                                 :direction="$sorts['description'] ?? null">Description
                </x-table.heading>

                <x-table.heading class="hidden md:table-cell"><span class="sr-only">Options</span></x-table.heading>

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
                                <span class="hidden md:block">{{ $row->name }}</span>
                                <div wire:click.prevent="edit('{{$row->id}}')"
                                     class="md:hidden grid grid-cols-1 cursor-pointer">
                                    <div class="col-span-1 -mt-1.5 truncate space-y-1">
                                        <div class="md:hidden truncate font-bold">{{ $row->name }}</div>
                                        <div class="truncate">{{ $row->description }}</div>
                                    </div>
                                </div>
                            </x-table.cell>

                            <x-table.cell
                                    class="hidden md:table-cell truncate">{{ $row->description }}</x-table.cell>
                            <x-table.cell class="hidden md:table-cell text-right">
                                <a href="{{route('admin.group.edit', ['id' => $row->id,'overview'])}}">
                                    <x-icon.edit/>
                                </a>

                            </x-table.cell>
                        </x-table.row>
                    @empty
                        <x-table.row>
                            <x-table.cell class="md:hidden">
                            <span class="w-full flex items-center justify-center space-x-2 py-4"><x-icon.emoji-sad
                                        size="6"/><span
                                        class="text-lg">{{__('empty-table.admin_provider',['attribute' => 'group'])}}</span></span>
                            </x-table.cell>
                            <x-table.cell class="hidden md:table-cell" colspan="5">
                            <span class="w-full flex items-center justify-center space-x-2 py-4"><x-icon.emoji-sad
                                        size="6"/><span
                                        class="text-lg">{{__('empty-table.admin_provider',['attribute' => 'groups'])}}</span></span>
                            </x-table.cell>
                        </x-table.row>
                    @endforelse
                </x-table.body>
            </x-slot>
        </x-table>
    </div>

    <!-- Section delete group-->
    <x-modal modal="delete">
        <form wire:submit.prevent="delete">
            <x-modal.panel class="md:max-w-lg space-y-4" type="warning" title="Are you sure?">
                <x-slot name="content">
                    <div class="text-xs md:text-base space-y-2">
                        @choice('modal.delete',count($objects), ['count' => count($objects),'attribute' => count($objects) <2 ?'group':'groups'])
                        @if(count($objects) == 1 )
                            <dl class=" mt-2">
                                <dt class="font-bold">{{  $objects ? $objects->first()->name : null }}</dt>
                                <dd class="mt-1 truncate">{{$objects ? $objects->first()->description : null}}</dd>
                            </dl>
                        @endif
                    </div>
                </x-slot>
                <x-slot name="button">
                    <x-btn.danger type="submit">Delete group</x-btn.danger>
                    <x-btn.secondary x-on:click="open=false">Cancel</x-btn.secondary>
                </x-slot>
            </x-modal.panel>
        </form>
    </x-modal>
</div>

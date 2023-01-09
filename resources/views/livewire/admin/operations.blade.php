<div class="content">
    <x-title>
        {{ __('Operations') }}
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
        <x-btn.danger wire:click="deleteModal"
                         class="{{empty($selected) ? 'hidden' : ''}}">{{ __('button.delete', ['attribute' => '']) }}</x-btn.danger>
        <x-btn.secondary wire:click.debounce="openCreateModal">{{ __('button.operation_create') }}</x-btn.secondary>
    </div>

    <!-- Section Main -->
    <div x-data="{ selectPagePopup:@entangle('selectPagePopup') }">
        <x-table class="md:table-auto md:max-w-screen-2xl">
            <x-slot name="head">
                <x-table.heading>
                    <x-input.checkbox wire:model="selectPage"/>
                </x-table.heading>

                <x-table.heading sortable multiColumn wire:click="sortBy('operation')"
                                 :direction="$sorts['operation'] ?? null">
                    Operation
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
                                    {{ __('messages.selected_operations', ['attribute' => count($selected)]) }}
                                <x-btn.link type="button"
                                            wire:click="selectAll">{{ __('messages.select_all', ['attribute' => $rows->total()]) }}</x-btn.link>
                                </span>
                            </div>
                        </x-table.cell>
                    </x-table.row>
                    @forelse($rows as $row)
                        <x-table.row>
                            <x-table.cell>
                                <x-input.checkbox value="{{ $row->id }}" x-model="$wire.selected"
                                                  class="'{{$selectPage?'checked':''}}'"/>
                            </x-table.cell>

                            <x-table.cell>
                                <span class="hidden md:block">{{ $row->operation }}</span>
                                <div wire:click.prevent="editModal('{{$row->id}}')"
                                     class="md:hidden grid grid-cols-1 cursor-pointer">
                                    <div class="col-span-1 -mt-1.5 truncate space-y-1">
                                        <div class="md:hidden truncate font-bold">{{ $row->operation }}</div>
                                        <div class="truncate">{{ $row->description }}</div>
                                    </div>
                                </div>
                            </x-table.cell>

                            <x-table.cell
                                    class="hidden md:table-cell truncate">{{ $row->description }}</x-table.cell>
                            <x-table.cell class="hidden md:table-cell text-right">
                                <x-btn.link type="button" wire:click.prevent="editModal('{{$row->id}}')">Edit
                                </x-btn.link>
                            </x-table.cell>
                        </x-table.row>
                    @empty
                        <x-table.row>
                            <x-table.cell class="md:hidden">
                            <span class="w-full flex items-center justify-center space-x-2 py-4"><x-icon.emoji-sad
                                        size="6"/><span
                                        class="text-lg">{{__('empty-table.admin_provider',['attribute' => 'operation'])}}</span></span>
                            </x-table.cell>
                            <x-table.cell class="hidden md:table-cell" colspan="5">
                            <span class="w-full flex items-center justify-center space-x-2 py-4"><x-icon.emoji-sad
                                        size="6"/><span
                                        class="text-lg">{{__('empty-table.admin_provider',['attribute' => 'operations'])}}</span></span>
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

    <!-- Section create operation-->
    <x-modal modal="create">
        <form wire:submit.prevent="save">
            <x-modal.panel class="md:max-w-6xl space-y-4" title="Create a Operation">
                <x-slot name="content">
                    <div class="sm:grid grid-cols-2 lg:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                        <x-input.group inline borderless for="operation" label="Operation"
                                       :error="$errors->first('operation.operation')"/>
                        <x-input.text name="name" class="w-full" wire:model="operation.operation"/>
                    </div>

                    <div class="sm:grid grid-cols-2 lg:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                        <x-input.group inline borderless for="auth_url" label="Description"
                                       :error="$errors->first('operation.description')"/>
                        <x-input.text name="auth_url" class="w-full" wire:model="operation.description"/>
                    </div>
                </x-slot>

                <x-slot name="button">
                    <x-btn.primary wire:click="closeModal">Cancel</x-btn.primary>
                    <x-btn.secondary type="submit">Create</x-btn.secondary>
                </x-slot>
            </x-modal.panel>
        </form>
    </x-modal>

    <!-- Section edit operation-->
    <x-modal modal="edit">
        @if($operation)
            <form wire:submit.prevent="save">
                <x-modal.panel class="md:max-w-6xl space-y-4" title="">
                    <x-slot name="content">
                        <div class="sm:grid grid-cols-2 lg:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                            <x-input.group inline borderless for="name" label="Operation"
                                           :error="$errors->first('operation.operation')"/>
                            <x-input.text name="name" class="w-full" wire:model="operation.operation"/>
                        </div>
                        <div class="sm:grid grid-cols-2 lg:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                            <x-input.group inline borderless for="auth_url" label="Description"
                                           :error="$errors->first('operation.description')"/>
                            <x-input.text name="auth_url" class="w-full" wire:model="operation.description"/>
                        </div>
                    </x-slot>
                    <x-slot name="button">
                        <x-btn.secondary type="submit">Save</x-btn.secondary>
                        <x-btn.secondary wire:click="closeModal">Cancel</x-btn.secondary>
                    </x-slot>
                </x-modal.panel>
            </form>
        @endif
    </x-modal>

    <!-- Section delete provider-->
    <x-modal modal="delete">
        <form wire:submit.prevent="deleteOperation">
            <x-modal.panel class="md:max-w-lg space-y-4" type="warning" title="Are you sure?">
                <x-slot name="content">
                    <div class="text-xs md:text-base space-y-2">
                        @choice('modal.delete',count($objects), ['count' => count($objects),'attribute' => count($objects) <2 ?'operation':'operations'])
                        @if(count($objects) == 1 )
                            <dl class=" mt-2">
                                <dt class="font-bold">{{  $objects ? $objects->first()->operation : null }}</dt>
                                <dd class="mt-1 truncate">{{$objects ? $objects->first()->description : null}}</dd>
                            </dl>
                        @endif
                    </div>
                </x-slot>
                <x-slot name="button">
                    <x-btn.danger type="submit">Delete operation</x-btn.danger>
                    <x-btn.secondary x-on:click="open=false">Cancel</x-btn.secondary>
                </x-slot>
            </x-modal.panel>
        </form>
    </x-modal>
</div>

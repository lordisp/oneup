<div class="content">
    <x-title>
        {{ __('Clients') }}
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
        @can('delete-client')
            <x-btn.danger wire:click="deleteModal"
                          class="{{empty($selected) ? 'hidden' : ''}}"
            >{{ __('button.delete_selected') }}</x-btn.danger>
        @endcan

        <x-btn.secondary x-data="{modal: 'create'}"
                         x-on:click="$dispatch('open-modal',{modal})"
        >{{ __('button.clients_create') }}</x-btn.secondary>
    </div>

    <div x-data="{ selectPagePopup:@entangle('selectPagePopup') }">
        <x-table class="table-auto md:table-auto max-w-screen-2xl">
            <x-slot name="head">
                <x-table.heading>
                    <x-input.checkbox wire:model="selectPage"/>
                </x-table.heading>
                <x-table.heading sortable multiColumn wire:click="sortBy('name')"
                                 :direction="$sorts['name'] ?? null">
                    Name
                </x-table.heading>
                <x-table.heading sortable multiColumn wire:click="sortBy('id')" :direction="$sorts['id'] ?? null"
                                 class="hidden md:table-cell">Client Id
                </x-table.heading>
                <x-table.heading sortable multiColumn wire:click="sortBy('redirect')"
                                 :direction="$sorts['redirect'] ?? null" class="hidden md:table-cell">Redirect-Uri
                </x-table.heading>
                <x-table.heading><span class="sr-only">Edit</span></x-table.heading>
            </x-slot>
            <x-slot name="body">
                <x-table.body>
                    <x-table.row>
                        <x-table.cell x-show="selectPagePopup"
                                      class="bg-lhg-yellow text-white font-bold top-0"
                                      colspan="5">
                            You are currently selecting {{count($selected) }} clients. Do you want to select all
                            <x-btn.link type="button" wire:click="selectAll">{{$rows->total()}}</x-btn.link>
                            ?
                            </span>
                        </x-table.cell>
                    </x-table.row>
                    @forelse($rows as $row)
                        <x-table.row>
                            <x-table.cell class="table-cell align-text-top">
                                <x-input.checkbox value="{{ $row->id }}" x-model="$wire.selected"
                                                  class="'{{$selectPage?'checked':''}}'"/>
                            </x-table.cell>
                            <x-table.cell class="hidden md:table-cell">
                                {{ $row->name }}
                            </x-table.cell>
                            <x-table.cell class="hidden md:table-cell truncate">
                                {{ $row->id }}
                            </x-table.cell>
                            <x-table.cell>
                                <span class="hidden md:table-cell truncate">{{ $row->redirect }}</span>
                                <span class="md:hidden font-bold md:font-normal">{{ $row->name }}</span>
                                <dl class="md:hidden">
                                    <dt class="sr-only">Client-Id</dt>
                                    <dd class="mt-1 truncate">{{ $row->id }}</dd>
                                </dl>
                                <dl class="md:hidden">
                                    <dt class="sr-only">Redirect URL</dt>
                                    <dd class="mt-1 truncate">{{ $row->redirect }}</dd>
                                </dl>
                                <dl class="md:hidden">
                                    <dt class="sr-only">Redirect URL</dt>
                                    <dd class="mt-1 truncate items-center flex justify-around py-2 border-t border-gray-500">
                                        <x-btn.link wire:click="deleteModal('{{$row->id}}')">Delete</x-btn.link>
                                    </dd>
                                </dl>
                            </x-table.cell>
                            <x-table.cell class="hidden md:table-cell text-right">
                                <x-btn.link type="button" wire:click.prevent="deleteModal('{{$row->id}}')">Delete</x-btn.link>
                            </x-table.cell>
                        </x-table.row>
                    @empty
                        <x-table.row>
                            <x-table.cell class="md:hidden">
                            <span class="w-full flex items-center justify-center space-x-2 py-4"><x-icon.emoji-sad
                                        size="6"/><span
                                        class="text-lg">{{__('empty-table.oauth_clients')}}</span></span>
                            </x-table.cell>
                            <x-table.cell class="hidden md:table-cell" colspan="5">
                            <span class="w-full flex items-center justify-center space-x-2 py-4"><x-icon.emoji-sad
                                        size="6"/><span
                                        class="text-lg">{{__('empty-table.oauth_clients')}}</span></span>
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

    <!-- Section Create new client-->
    <x-modal modal="create">
        <form wire:submit.prevent="createClient">
            <x-modal.panel class="md:max-w-6xl space-y-4" title="Create Client">
                <x-slot name="content">
                    <div class="sm:grid grid-cols-2 lg:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                        <x-input.group inline borderless for="name" label="Name" :error="$errors->first('name')"/>
                        <x-input.text name="name" class="w-full" wire:model="name"/>
                    </div>
                    <div class="sm:grid grid-cols-2 lg:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                        <x-input.group inline
                                       helptextinline
                                       borderless
                                       for="redirect"
                                       label="Redirect-Uri"
                                       :error="$errors->first('redirect')"
                        >
                            <x-slot name="helpText">
                                <button type="button" x-tooltip="{{__('help-text.redirect_uri')}}">
                                    <x-icon.question-mark-circle solid class="text-gray-500"/>
                                </button>
                            </x-slot>
                        </x-input.group>
                        <x-input.text name="redirect" class="w-full" wire:model="redirect"/>
                    </div>
                    <div class="sm:grid grid-cols-2 lg:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                        <x-input.group inline
                                       helptextinline
                                       borderless
                                       for="confidential"
                                       label="PKCE authorization code grant"
                                       :error="$errors->first('confidential')">
                            <x-slot name="helpText">
                                <button type="button" x-tooltip="{{__('help-text.pkce')}}">
                                    <x-icon.question-mark-circle solid class="text-gray-500"/>
                                </button>
                            </x-slot>
                        </x-input.group>
                        <x-input.checkbox name="confidential" wire:model="confidential"/>
                    </div>
                </x-slot>
                <x-slot name="button">
                    <x-btn.secondary x-on:click="open=false">Cancel</x-btn.secondary>
                    <x-btn.primary type="submit">{{__('button.save')}}</x-btn.primary>
                </x-slot>
            </x-modal.panel>
        </form>
    </x-modal>

    <!-- Section Show Secret-->
    <x-modal modal="secret">
        <x-modal.panel class="md:max-w-6xl space-y-4" title="Client has been created!">
            <x-slot name="content">
                <div class="sm:grid grid-cols-2 lg:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                    <div class="font-medium leading-5 text-gray-700 dark:text-gray-400">Client-Id</div>
                    <div>{{  data_get($secret,'id') }}</div>
                </div>
                <div class="sm:grid grid-cols-2 lg:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                    <x-input.group inline borderless for="Name" label="Name"/>
                    {{ data_get($secret,'name') }}
                </div>
                <div class="sm:grid grid-cols-2 lg:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                    <x-input.group inline borderless for="redirect" label="Redirect-Uri"/>
                    {{ data_get($secret,'redirect') }}
                </div>
                @if(!empty( data_get($secret,'secret')))
                    <div class="sm:grid grid-cols-2 lg:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                        <x-input.group inline borderless for="secret" label="Secret"/>
                        <b class="break-all">
                            {{ data_get($secret,'secret') }}
                        </b>
                    </div>
                    <div class="border-l-2 border-red-400 bg-gray-100 dark:bg-gray-600 pl-2 py-2">
                        <i>
                            Client secret values cannot be viewed, except for immediately after creation. Be sure to
                            save
                            the secret when created before leaving the page.
                        </i>
                    </div>
                @endif
            </x-slot>
            <x-slot name="button">
                <x-btn.secondary x-on:click="open=false">Close</x-btn.secondary>
            </x-slot>
        </x-modal.panel>
    </x-modal>

    <!-- Section delete client modal-->
    <x-modal modal="delete" target="createClient">
        <form wire:submit.prevent="deleteClient">
            <x-modal.panel class="md:max-w-lg space-y-4" type="warning" title="Are you sure?">
                <x-slot name="content">
                    <div class="text-xs md:text-base space-y-2">
                        @choice('modal.delete_clients',count($clients), ['count' => count($clients)])
                        @if(count($clients) == 1 )
                            <dl class=" mt-2">
                                <dt class="font-bold">Name:</dt>
                                <dd class="mt-1 truncate">{{  $clients ? $clients->first()->name : null }}</dd>
                            </dl>
                            <dl>
                                <dt class="font-bold">Client Id:</dt>
                                <dd class="mt-1 truncate">{{$clients ? $clients->first()->id : null}}</dd>
                            </dl>
                            <dl>
                                <dt class="font-bold">Redirect URL:</dt>
                                <dd class="mt-1 truncate">{{$clients ? $clients->first()->redirect : null}}</dd>
                            </dl>
                        @endif
                    </div>
                </x-slot>
                <x-slot name="button">
                    <x-btn.danger type="submit">Delete client</x-btn.danger>
                    <x-btn.secondary x-on:click="open=false">Cancel</x-btn.secondary>
                </x-slot>
            </x-modal.panel>
        </form>
    </x-modal>
</div>
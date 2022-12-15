<div class="content">
    <x-title>
        {{ __('Provider') }}
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
        @can('provider-delete')
            <x-btn.secondary wire:click="deleteModal"
                             class="{{empty($selected) ? 'hidden' : ''}}"
            >{{ __('button.provider_bulk_delete') }}</x-btn.secondary>
        @endcan

        <x-btn.secondary wire:click.debounce="openCreateModal"
        >{{ __('button.provider_create') }}</x-btn.secondary>
    </div>

    <div x-data="{ selectPagePopup:@entangle('selectPagePopup') }">
        <x-table class="md:table-auto md:max-w-screen-2xl">
            <x-slot name="head">
                <x-table.heading>
                    <x-input.checkbox wire:model="selectPage"/>
                </x-table.heading>

                <x-table.heading sortable multiColumn wire:click="sortBy('name')" :direction="$sorts['name'] ?? null">
                    Name
                </x-table.heading>

                <x-table.heading class="hidden md:table-cell" sortable multiColumn wire:click="sortBy('tenant')"
                                 :direction="$sorts['tenant'] ?? null">Tenant
                </x-table.heading>

                <x-table.heading class="hidden lg:table-cell" sortable multiColumn wire:click="sortBy('client_id')"
                                 :direction="$sorts['client_id'] ?? null">Client Id
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
                                    {{ __('messages.selected_providers', ['attribute' => count($selected)]) }}
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
                                <span class="hidden md:block">{{ $row->name }}</span>
                                <div class="lg:hidden grid grid-cols-1">
                                    <div class="col-span-1 -mt-1.5 truncate space-y-1">
                                        <div class="md:hidden font-bold">{{ $row->name }}</div>
                                        <div class="hidden lg:block">{{ json_decode($row->client)->tenant }}</div>
                                        <div class="truncate">{{ json_decode($row->client)->client_id }}</div>
                                        @if(array_key_exists('resource',json_decode($row->client,true)))
                                            <div class="truncate">{{ json_decode($row->client)->resource }}</div>
                                        @endif
                                        @if(array_key_exists('scope',json_decode($row->client,true)))
                                            <div class="truncate">{{ json_decode($row->client)->scope }}</div>
                                        @endif
                                        <div class="truncate">{{ json_decode($row->client)->client_id }}</div>
                                    </div>
                                </div>
                            </x-table.cell>

                            <x-table.cell
                                    class="hidden md:table-cell truncate">{{ json_decode($row->client)->tenant }}</x-table.cell>

                            <x-table.cell
                                    class="hidden lg:table-cell truncate">{{ json_decode($row->client)->client_id }}</x-table.cell>

                            <x-table.cell class="hidden md:table-cell text-right">
                                <x-btn.link type="button" wire:click.prevent="editModal('{{$row->id}}')">Details
                                </x-btn.link>
                            </x-table.cell>
                        </x-table.row>
                    @empty
                        <x-table.row>
                            <x-table.cell class="md:hidden">
                            <span class="w-full flex items-center justify-center space-x-2 py-4"><x-icon.emoji-sad
                                        size="6"/><span
                                        class="text-lg">{{__('empty-table.admin_provider',['attribute' => 'providers'])}}</span></span>
                            </x-table.cell>
                            <x-table.cell class="hidden md:table-cell" colspan="5">
                            <span class="w-full flex items-center justify-center space-x-2 py-4"><x-icon.emoji-sad
                                        size="6"/><span
                                        class="text-lg">{{__('empty-table.admin_provider',['attribute' => 'providers'])}}</span></span>
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

    <!-- Section edit provider-->
    <x-modal modal="edit">
        @if($provider)
            <form wire:submit.prevent="save">

                <x-modal.panel class="md:max-w-6xl space-y-4" title="">

                    <x-slot name="content">
                        <div class="sm:grid grid-cols-2 lg:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                            <x-input.group inline borderless for="name" label="Name"
                                           :error="$errors->first('provider.name')"/>
                            <x-input.text name="name" class="w-full" wire:model="provider.name"/>
                        </div>

                        <div class="sm:grid grid-cols-2 lg:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                            <x-input.group inline borderless for="auth_url" label="Authorization Url"
                                           :error="$errors->first('provider.auth_url')"/>
                            <x-input.text name="auth_url" class="w-full" wire:model="provider.auth_url"/>
                        </div>

                        <div class="sm:grid grid-cols-2 lg:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                            <x-input.group inline borderless for="token_url" label="Token Url"
                                           :error="$errors->first('provider.token_url')"/>
                            <x-input.text name="token_url" class="w-full" wire:model="provider.token_url"/>
                        </div>

                        <div class="sm:grid grid-cols-2 lg:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                            <x-input.group inline borderless for="auth_endpoint" label="Authorization Endpoint"
                                           :error="$errors->first('provider.auth_endpoint')"/>
                            <x-input.text name="auth_endpoint" class="w-full" wire:model="provider.auth_endpoint"/>
                        </div>

                        <!-- Client -->
                        <div class="sm:grid grid-cols-2 lg:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                            <x-input.group inline borderless for="tenant" label="Tenant"
                                           :error="$errors->first('client.tenant')"/>
                            <x-input.text name="tenant" class="w-full" wire:model="client.tenant"/>
                        </div>

                        <div class="sm:grid grid-cols-2 lg:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                            <x-input.group inline borderless for="client_id" label="Client Id"
                                           :error="$errors->first('client.client_id')"/>
                            <x-input.text name="client_id" class="w-full" wire:model="client.client_id"/>
                        </div>

                        <div class="sm:grid grid-cols-2 lg:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                            <x-input.group inline borderless for="client_secret" label="Client Secret"
                                           :error="$errors->first('client.client_secret')"/>
                            <x-input.text name="client_secret" class="w-full" wire:model="client.client_secret"/>
                        </div>

                        @if(array_key_exists('scope',$client))
                            <div class="sm:grid grid-cols-2 lg:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                                <x-input.group inline borderless for="scope" label="Scope"
                                               :error="$errors->first('client.scope')"/>
                                <x-input.text name="scope" class="w-full" wire:model="client.scope"/>
                            </div>
                        @endif

                        @if(array_key_exists('resource',$client))
                            <div class="sm:grid grid-cols-2 lg:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                                <x-input.group inline borderless for="resource" label="Resource"
                                               :error="$errors->first('client.resource')"/>
                                <x-input.text name="resource" class="w-full" wire:model="client.resource"/>
                            </div>
                        @endif

                    </x-slot>

                    <x-slot name="button">
                        <x-btn.secondary type="submit">Save</x-btn.secondary>
                        <x-btn.secondary wire:click="closeModal">Cancel</x-btn.secondary>
                    </x-slot>

                </x-modal.panel>

            </form>
        @endif
    </x-modal>

    <!-- Section create provider-->
    <x-modal modal="create">

        <form wire:submit.prevent="save">

            <x-modal.panel class="md:max-w-6xl space-y-4" title="Create a Provider">

                <x-slot name="content">
                    <div class="sm:grid grid-cols-2 lg:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                        <x-input.group inline borderless for="name" label="Name"
                                       :error="$errors->first('provider.name')"/>
                        <x-input.text name="name" class="w-full" wire:model="provider.name"/>
                    </div>

                    <div class="sm:grid grid-cols-2 lg:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                        <x-input.group inline borderless for="auth_url" label="Authorization Url"
                                       :error="$errors->first('provider.auth_url')"/>
                        <x-input.text name="auth_url" class="w-full" wire:model="provider.auth_url"/>
                    </div>

                    <div class="sm:grid grid-cols-2 lg:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                        <x-input.group inline borderless for="token_url" label="Token Url"
                                       :error="$errors->first('provider.token_url')"/>
                        <x-input.text name="token_url" class="w-full" wire:model="provider.token_url"/>
                    </div>

                    <div class="sm:grid grid-cols-2 lg:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                        <x-input.group inline borderless for="auth_endpoint" label="Authorization Endpoint"
                                       :error="$errors->first('provider.auth_endpoint')"/>
                        <x-input.text name="auth_endpoint" class="w-full" wire:model="provider.auth_endpoint"/>
                    </div>

                    <h3>Client</h3>
                    <!-- Client -->
                    <div class="sm:grid grid-cols-2 lg:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                        <x-input.group inline borderless for="type" label="API Type"
                                       :error="$errors->first('type')"/>
                        <x-input.select placeholder="select one.." wire:model="type">
                            <option value="arm">Azure Resource Manager API</option>
                            <option value="graph">Azure Graph API</option>
                        </x-input.select>
                    </div>

                    <div class="sm:grid grid-cols-2 lg:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                        <x-input.group inline borderless for="tenant" label="Tenant"
                                       :error="$errors->first('client.tenant')"/>
                        <x-input.text name="tenant" class="w-full" wire:model="client.tenant"/>
                    </div>

                    <div class="sm:grid grid-cols-2 lg:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                        <x-input.group inline borderless for="client_id" label="Client Id"
                                       :error="$errors->first('client.client_id')"/>
                        <x-input.text name="client_id" class="w-full" wire:model="client.client_id"/>
                    </div>

                    <div class="sm:grid grid-cols-2 lg:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                        <x-input.group inline borderless for="client_secret" label="Client Secret"
                                       :error="$errors->first('client.client_secret')"/>
                        <x-input.text name="client_secret" class="w-full" wire:model="client.client_secret"/>
                    </div>

                    @if($type=='graph')
                        <div class="sm:grid grid-cols-2 lg:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                            <x-input.group inline borderless for="scope" label="Scope"
                                           :error="$errors->first('client.scope')"/>
                            <x-input.text name="scope" class="w-full" wire:model="client.scope"/>
                        </div>
                    @endif

                    @if($type=='arm')
                        <div class="sm:grid grid-cols-2 lg:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                            <x-input.group inline borderless for="resource" label="Resource"
                                           :error="$errors->first('client.resource')"/>
                            <x-input.text name="resource" class="w-full" wire:model="client.resource"/>
                        </div>
                    @endif

                </x-slot>

                <x-slot name="button">
                    <x-btn.secondary type="submit">Create</x-btn.secondary>
                    <x-btn.secondary wire:click="closeModal">Cancel</x-btn.secondary>
                </x-slot>

            </x-modal.panel>

        </form>

    </x-modal>

    <!-- Section delete provider-->
    <x-modal modal="delete">
        <form wire:submit.prevent="deleteProvider">
            <x-modal.panel class="md:max-w-lg space-y-4" type="warning" title="Are you sure?">
                <x-slot name="content">
                    <div class="text-xs md:text-base space-y-2">
                        @choice('modal.delete',count($objects), ['count' => count($objects),'attribute' => count($objects) <2 ?'provider':'providers'])
                        @if(count($objects) == 1 )
                            <dl class=" mt-2">
                                <dt class="font-bold">{{  $objects ? $objects->first()->name : null }}</dt>
                                <dd class="mt-1 truncate">{{$objects ? $objects->first()->redirect : null}}</dd>
                            </dl>
                            <dl>
                                <dd class="mt-1 truncate">{{$objects ? $objects->first()->id : null}}</dd>
                            </dl>
                            <dl>
                                <dd class="mt-1 truncate">{{$objects ? $objects->first()->redirect : null}}</dd>
                            </dl>
                        @endif
                    </div>
                </x-slot>
                <x-slot name="button">
                    <x-btn.danger type="submit">Delete provider</x-btn.danger>
                    <x-btn.secondary x-on:click="open=false">Cancel</x-btn.secondary>
                </x-slot>
            </x-modal.panel>
        </form>
    </x-modal>
</div>
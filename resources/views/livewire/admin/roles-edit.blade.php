<div class="content">
    <x-title>
        {{ request()->is('admin/roles/create') ? __('Create Role') : __('Edit Role') }}
    </x-title>

    <x-card.gray class="rounded-lg">
        <form wire:submit.prevent="save">
            <x-card.fields>
                <x-slot name="title">Role Details</x-slot>
                <x-slot name="subtitle">Add a role name and description.</x-slot>
                <x-card.form class="w-full">
                    <div class="gap-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-1 lg:grid-cols-2 ">
                        <x-input.group inline for="name" label="Name" :error="$errors->first('role.name')">
                            <x-input.text wire:model.defer="role.name" class="w-full" autocomplete="off"/>
                        </x-input.group>
                        <x-input.group inline for="description" label="Description"
                                       :error="$errors->first('role.description')">
                            <x-input.text wire:model.defer="role.description" class="w-full" autocomplete="off"/>
                        </x-input.group>
                    </div>
                </x-card.form>
            </x-card.fields>

            <x-card.fields>
                <x-slot name="title">Operations</x-slot>
                <x-slot name="subtitle">Decide which operations the role shall be allowed to execute.</x-slot>
                <x-card.form buttons search paddingless id="search">
                    <div class="flex-1 min-w-0 space-y-2 sm:px-4">
                        <x-input.group inline for="operation" label="" :error="$errors->first('selected')">
                            <x-table sticky="false" class="w-full">
                                <x-slot name="head">
                                    <x-table.heading>
                                        <x-input.checkbox wire:model="selectPage"/>
                                    </x-table.heading>
                                    <x-table.heading>Operation</x-table.heading>
                                    <x-table.heading class="hidden lg:table-cell">Description</x-table.heading>
                                </x-slot>
                                <x-slot name="body">
                                    <x-table.body>
                                        @forelse($rows as $row)
                                            <x-table.row>
                                                <x-table.cell class="w-8">
                                                    <x-input.checkbox value="{{ $row->id }}" x-model="$wire.selected" class="'{{$selectPage?'checked':''}}'"/>
                                                </x-table.cell>
                                                <x-table.cell class="flex flex-col">
                                                    <span>{{$row['operation']}}</span>
                                                    <span class="lg:hidden">{{$row['description']}}</span>
                                                </x-table.cell>
                                                <x-table.cell class="hidden lg:table-cell">{{$row['description']}}</x-table.cell>
                                            </x-table.row>
                                        @empty
                                            <x-table.row>
                                                <x-table.cell colspan="3" class="bg-white space-y-4 text-center py-4">
                                                    <div class="flex justify-center items-center space-x-2 text-gray-400">
                                                        <x-icon.document-search size="7"/>
                                                        <h3>No Operations found</h3>
                                                    </div>
                                                    <a class="btn-secondary" href="{{route('admin.roles.create')}}">Create
                                                        new Operations</a>
                                                </x-table.cell>
                                            </x-table.row>
                                        @endforelse
                                    </x-table.body>
                                </x-slot>
                            </x-table>
                        </x-input.group>
                        <div class="px-4">
                            {{ $rows->onEachSide(2)->links('components/paginate-sm') }}
                        </div>
                        <x-slot name="buttons">
                            <x-btn.secondary wire:click="cancel">
                                Cancel
                            </x-btn.secondary>
                            <x-btn.primary type="submit">
                                Save
                            </x-btn.primary>
                        </x-slot>
                    </div>
                </x-card.form>
            </x-card.fields>
        </form>
    </x-card.gray>
</div>

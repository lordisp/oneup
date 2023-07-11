<div class="content">
    <x-title>
        {{ __('Create Group') }}
    </x-title>
    <x-card.gray class="rounded-lg">
        <div x-data="{roles: false, owners: false, member:false }">
            <x-card.fields>
                <x-slot name="title">Group Details</x-slot>
                <x-slot name="subtitle">Name the group and provide a functional description</x-slot>
                <form wire:submit.prevent="save">
                    <x-card.form justify="between" buttons>
                        <div class="gap-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-1 lg:grid-cols-2 ">
                            <x-input.group inline for="name" label="Name" :error="$errors->first('group.name')">
                                <x-input.text wire:model.defer="group.name" class="w-full" id="name"
                                              autocomplete="off"/>
                            </x-input.group>
                            <x-input.group inline for="description" label="Description"
                                           :error="$errors->first('group.description')">
                                <x-input.text wire:model.defer="group.description" class="w-full" id="description"
                                              autocomplete="off"/>
                            </x-input.group>
                        </div>

                        @if($memberAssigment)
                            <x-divider class="my-10 text-sm"><strong>Add Member</strong></x-divider>

                            <div class="gap-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-1 lg:grid-cols-2 ">
                                <x-input.group inline for="user" label="Owners" :error="$errors->first('owner')">
                                    @if($owner)
                                        <x-btn.link wire:click="mode('owner')"
                                                    x-on:click.prevent="owners = true">{{count($owner)}} selected
                                            owner{{count($owner) > 1 ? "s": null}}</x-btn.link>
                                    @else
                                        <x-btn.link wire:click="mode('owner')" x-on:click.prevent="owners = true">No
                                            owners selected
                                        </x-btn.link>
                                    @endif
                                </x-input.group>

                                <x-input.group inline for="selected" label="Members">
                                    @if($member)
                                        <x-btn.link wire:click="mode('member')"
                                                    x-on:click.prevent="member = true">{{count($member)}} selected
                                            member{{count($member) > 1 ? "s": null}}</x-btn.link>
                                    @else
                                        <x-btn.link wire:click="mode('member')" x-on:click.prevent="member = true">No
                                            members selected
                                        </x-btn.link>
                                    @endif
                                </x-input.group>
                            </div>

                            <!-- Section Slide-Over "Add Owners" -->
                            <div>
                                <x-slide-over show="owners" withoutSave cancel="{{ __('button.close') }}" class="max-h-screen  overflow-hidden">
                                    <x-slot name="title">Add Owners</x-slot>
                                    <x-slot name="content">
                                        <x-input.text type="search" class="w-full" wire:model="search"></x-input.text>
                                        <div class="h-1/2 overflow-y-auto py-1">
                                            @if(!empty($results))
                                                <x-list>
                                                    @forelse($results as $result)
                                                        <x-list.sticky wire:click.prevent="add({{json_encode($result)}},'owner')" size="2" withImage="true" secondline>
                                                            <x-slot name="image">
                                                                <img class="h-10 w-10 rounded-full"
                                                                     src="{{isset($result['avatar']) ? $result['avatar'] : 'https://ui-avatars.com/api/?name='.urlencode($result['displayName']).'&color=7F9CF5&background=random'}}"
                                                                     alt="{{$result['displayName']}}">
                                                            </x-slot>
                                                            <x-slot name="first">
                                                                {{$result['displayName']}}
                                                            </x-slot>
                                                            <x-slot name="second">
                                                                {{strtolower($result['email'])}}
                                                            </x-slot>
                                                        </x-list.sticky>
                                                    @empty
                                                        <div class="flex italic justify-center pt-2 text-sm">No results found ...</div>
                                                    @endforelse
                                                </x-list>
                                            @endif
                                        </div>
                                        <x-divider class="py-4">Selected</x-divider>
                                        <div class="h-1/2 overflow-y-auto py-1">
                                            @if(!empty($owner))
                                                <x-list>
                                                    @forelse($owner as $owner)
                                                        <x-list.sticky wire:click.prevent="remove({{json_encode($owner)}},'owner')" size="2" withImage="true" secondline>
                                                            <x-slot name="image">
                                                                <img class="h-10 w-10 rounded-full"
                                                                     src="{{isset($owner['avatar']) ? $owner['avatar'] : 'https://ui-avatars.com/api/?name='.urlencode($owner['displayName']).'&color=7F9CF5&background=random'}}"
                                                                     alt="{{$owner['displayName']}}">

                                                            </x-slot>
                                                            <x-slot name="first">
                                                                {{$owner['displayName']}}
                                                            </x-slot>
                                                            <x-slot name="second">
                                                                {{strtolower($owner['email'])}}
                                                            </x-slot>
                                                        </x-list.sticky>
                                                    @empty
                                                        <div class="flex italic justify-center pt-2 text-sm">No results found ...</div>
                                                    @endforelse
                                                </x-list>
                                            @endif
                                        </div>
                                    </x-slot>
                                </x-slide-over>
                            </div>

                            <!-- Section Slide-Over "Add Member" -->
                            <div>
                                <x-slide-over show="member" withoutSave cancel="{{ __('button.close') }}" class="max-h-screen  overflow-hidden">
                                    <x-slot name="title">Add Member</x-slot>
                                    <x-slot name="content">
                                        <x-input.text type="search" class="w-full" wire:model="search"></x-input.text>
                                        <div class="h-1/2 overflow-y-auto py-1">
                                            @if(!empty($results))
                                                <x-list>
                                                    @forelse($results as $result)
                                                        <x-list.sticky wire:click.prevent="add({{json_encode($result)}},'member')" size="2" withImage="true" secondline>
                                                            <x-slot name="image">
                                                                <img class="h-10 w-10 rounded-full"
                                                                     src="{{isset($result['avatar']) ? $result['avatar'] : 'https://ui-avatars.com/api/?name='.urlencode($result['displayName']).'&color=7F9CF5&background=random'}}"
                                                                     alt="{{$result['displayName']}}">
                                                            </x-slot>
                                                            <x-slot name="first">
                                                                {{$result['displayName']}}
                                                            </x-slot>
                                                            <x-slot name="second">
                                                                {{strtolower($result['email'])}}
                                                            </x-slot>
                                                        </x-list.sticky>
                                                    @empty
                                                        <div class="flex italic justify-center pt-2 text-sm">No results found ...</div>
                                                    @endforelse
                                                </x-list>
                                            @endif
                                        </div>
                                        <x-divider class="py-4">Selected</x-divider>
                                        <div class="h-1/2 overflow-y-auto py-1">
                                            @if(!empty($member))
                                                <x-list>
                                                    @forelse($member as $member)
                                                        <x-list.sticky wire:click.prevent="remove({{json_encode($member)}},'member')" size="2" withImage="true" secondline>
                                                            <x-slot name="image">
                                                                <img class="h-10 w-10 rounded-full"
                                                                     src="{{isset($member['avatar']) ? $member['avatar'] : 'https://ui-avatars.com/api/?name='.urlencode($member['displayName']).'&color=7F9CF5&background=random'}}"
                                                                     alt="{{$member['displayName']}}">
                                                            </x-slot>
                                                            <x-slot name="first">
                                                                {{$member['displayName']}}
                                                            </x-slot>
                                                            <x-slot name="second">
                                                                {{strtolower($member['email'])}}
                                                            </x-slot>
                                                        </x-list.sticky>
                                                    @empty
                                                        <div class="flex italic justify-center pt-2 text-sm">No results found ...</div>
                                                    @endforelse
                                                </x-list>
                                            @endif
                                        </div>
                                    </x-slot>
                                </x-slide-over>
                            </div>
                        @endif

                        <x-slot name="buttons">
                            <div class="sm:flex justify-start sm:space-x-3">
                                <x-btn.toggle wire:click.prevent="memberAssigment" label="Add Members?"/>
                                <x-btn.toggle wire:click.prevent="roleAssigment" label="Add Roles?"/>
                            </div>
                            <span>
                                <a class="btn-secondary" href="{{route('admin.group')}}">Cancel</a>
                                <x-btn.primary type="submit">
                                    Save
                                </x-btn.primary>
                            </span>
                        </x-slot>
                    </x-card.form>
                </form>
            </x-card.fields>
            @if($roleAssigment)
                <x-card.fields>
                    <x-slot name="title">Role Assigment</x-slot>
                    <x-slot name="subtitle">Manage role assigment for this group.</x-slot>
                    <x-card.form buttons paddingless >
                        <div class="flex-1 min-w-0 space-y-2 sm:px-4">
                            <x-table class="w-full">
                                <x-slot name="head">
                                    <x-table.heading>Name</x-table.heading>
                                    <x-table.heading>Description</x-table.heading>
                                </x-slot>
                                <x-slot name="body">
                                    <x-table.body>
                                        @if($assignedRoles)
                                            @forelse($assignedRoles as $role)
                                                <x-table.row class="hover:bg-gray-50">
                                                    <x-table.cell>
                                                        <div class="flex justify-start items-center space-x-2">
                                                            <img class="h-6 w-6 rounded-sm"
                                                                 src="{{'https://ui-avatars.com/api/?name='.urlencode($role['name']).'&color=7F9CF5&background=random'}}"
                                                                 alt="{{$role['name']}}">
                                                            <span>{{$role['name']}}</span>
                                                        </div>
                                                    </x-table.cell>
                                                    <x-table.cell>
                                                        {{$role['description']}}
                                                    </x-table.cell>
                                                </x-table.row>
                                            @empty
                                                <x-table.row>
                                                    <x-table.cell colspan="3" class="bg-white space-y-4 text-center py-4">
                                                        <div class="flex justify-center items-center space-x-2 text-gray-400">
                                                            <x-icon.document-search size="7"/>
                                                            <h3>Role not found!</h3>
                                                        </div>
                                                    </x-table.cell>
                                                </x-table.row>
                                            @endforelse
                                        @else
                                            <x-table.row>
                                                <x-table.cell colspan="3" class="space-y-4 text-center py-4">
                                                    <div class="flex justify-center items-center space-x-2 text-gray-400">
                                                        <x-icon.document-search size="7"/>
                                                        <h3>Start adding roles...</h3>
                                                    </div>
                                                </x-table.cell>
                                            </x-table.row>
                                        @endif
                                    </x-table.body>
                                </x-slot>
                            </x-table>
                            <!-- Section Slide-Over "Roles" -->
                            <div>
                                <form wire:submit.prevent="roleAssigment('attach')">
                                    <x-slide-over show="roles" cancel="{{ __('button.cancel') }}" submit="{{ __('button.apply') }}" class="max-h-screen  overflow-hidden">
                                        <x-slot name="title">Roles</x-slot>
                                        <x-slot name="content">
                                            <x-input.text type="search" class="w-full" wire:model="search"></x-input.text>
                                            <div class="h-1/2 overflow-y-auto py-1">
                                                @if(!empty($results))
                                                    <x-list>
                                                        @forelse($results as $result)
                                                            <x-list.sticky wire:click.prevent="add({{json_encode($result)}},'role')" size="2" withImage="true" secondline>
                                                                <x-slot name="image">
                                                                    <img class="h-10 w-10 rounded-full"
                                                                         src="{{'https://ui-avatars.com/api/?name='.urlencode($result['name']).'&color=7F9CF5&background=random'}}"
                                                                         alt="{{$result['name']}}">
                                                                </x-slot>
                                                                <x-slot name="first">
                                                                    {{$result['name']}}
                                                                </x-slot>
                                                                <x-slot name="second">
                                                                    {{strtolower($result['description'])}}
                                                                </x-slot>
                                                            </x-list.sticky>
                                                        @empty
                                                            <div class="flex italic justify-center pt-2 text-sm">No results found ...</div>
                                                        @endforelse
                                                    </x-list>
                                                @endif
                                            </div>
                                            <x-divider class="py-4">Selected</x-divider>
                                            <div class="h-1/2 overflow-y-auto py-1">
                                                @if(!empty($roles))
                                                    <x-list>
                                                        @forelse($roles as $role)
                                                            <x-list.sticky wire:click.prevent="remove({{json_encode($role)}},'role')" size="2" withImage="true" secondline>
                                                                <x-slot name="image">
                                                                    <img class="h-10 w-10 rounded-full"
                                                                         src="{{'https://ui-avatars.com/api/?name='.urlencode($role['name']).'&color=7F9CF5&background=random'}}"
                                                                         alt="{{$role['name']}}">

                                                                </x-slot>
                                                                <x-slot name="first">
                                                                    {{$role['name']}}
                                                                </x-slot>
                                                                <x-slot name="second">
                                                                    {{strtolower($role['description'])}}
                                                                </x-slot>
                                                            </x-list.sticky>
                                                        @empty
                                                            <div class="flex italic justify-center pt-2 text-sm">No results found ...</div>
                                                        @endforelse
                                                    </x-list>
                                                @endif
                                            </div>
                                        </x-slot>
                                    </x-slide-over>
                                </form>
                            </div>
                            <x-slot name="buttons">
                                <x-btn.secondary wire:click.prevent="mode('roles')" x-on:click.prevent="roles = true">
                                    Roles
                                </x-btn.secondary>
                            </x-slot>
                        </div>
                    </x-card.form>
                </x-card.fields>
            @endif
        </div>
    </x-card.gray>
</div>

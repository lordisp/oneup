<div class="content">
    <x-title action>
        {{ __('Users') }}
        <x-slot name="action">
            <x-input.search wire:model.debounce.500ms="search" placeholder="Search..."/>
            <x-input.select class="w-20" wire:model="perPage" id="perPage">
                <option>15</option>
                <option>50</option>
                <option>100</option>
            </x-input.select>
        </x-slot>
    </x-title>
    <x-table class="resizeable">
        <x-slot name="head">
            <x-table.heading class="w-10 hidden md:table-cell" sortable multiColumn wire:click="sortBy('firstName')" :direction="$sorts['firstName'] ?? null">First name</x-table.heading>
            <x-table.heading sortable multiColumn wire:click="sortBy('lastName')" :direction="$sorts['lastName'] ?? null">Name</x-table.heading>
            <x-table.heading class="hidden md:table-cell" sortable multiColumn wire:click="sortBy('email')" :direction="$sorts['email'] ?? null">Email</x-table.heading>
            <x-table.heading class="hidden md:table-cell">
                <span class="sr-only">Edit</span>
            </x-table.heading>
        </x-slot>
        <x-slot name="body">
            <x-table.body>
                @forelse($users as $user)
                    <x-table.row>
                        <x-table.cell class="hidden md:table-cell">
                            {{ $user->firstName }}
                        </x-table.cell>
                        <x-table.cell class="hidden md:table-cell">
                            {{ $user->lastName }}
                        </x-table.cell>
                        <x-table.cell>
                            <span class="hidden md:block">{{ $user->email }}</span>
                            <span class="md:hidden">{{ $user->firstName." ".$user->lastName }}</span>
                            <dl class="font-normal md:hidden">
                                <dt class="sr-only">Email</dt>
                                <dd class="mt-1 truncate">{{ $user->email }}</dd>
                            </dl>
                        </x-table.cell>
                        <x-table.cell class="hidden md:table-cell text-right">
                            @if($user->id!=auth()->id())
                                <x-btn.link disabled="{{$user->id===auth()->id()}}" wire:click.prevent="loginAs('{{$user->id}}')">Login as</x-btn.link>
                                <x-btn.link wire:click.prevent="openLogoutUserModal('{{$user->id}}')">Logout</x-btn.link>
                            @endif

                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell class="md:hidden">
                            <span class="w-full flex items-center justify-center space-x-2 py-4"><x-icon.emoji-sad size="6"/><span class="text-lg">No records found!</span></span>
                        </x-table.cell>
                        <x-table.cell class="hidden md:table-cell" colspan="4">
                            <span class="w-full flex items-center justify-center space-x-2 py-4"><x-icon.emoji-sad size="6"/><span class="text-lg">No records found!</span></span>
                        </x-table.cell>
                    </x-table.row>
                @endforelse
            </x-table.body>
        </x-slot>
    </x-table>
    <div class="text-xs flex justify-center items-center md:justify-start md:items-start">Showing {{{ $users->count() ." of ". $users->total() }}}</div>
    <div>{{ $users->onEachSide(2)->links('components/paginate') }}</div>

    <!-- Section logout-user-->

    <x-modal modal="logout-user">

        <form wire:submit.prevent="logoutUser">

            <x-modal.panel type="warning" class="md:max-w-xl space-y-4" title="{{__('lines.are_you_sure')}}">

                <x-slot name="content">
                    @if($modalUser)
                    <div x-data="{modalLock:@entangle('modalLock')}"
                         class="space-y-4"
                    >
                        You're about to logout {{$modalUser->firstName}} from all devices.
                        <div class="flex space-y-2">
                            <label class="mt-2 inline-flex items-center">
                                <x-input.checkbox name="modalLock"
                                                  :class="$modalUser->status ? '':'cursor-not-allowed'"
                                                  :disabled="!$modalUser->status"
                                                  wire:model="modalLock"
                                />
                                <div class="ml-6" :class="{{$modalUser->status}} ? '':'cursor-not-allowed italic'" >Prevent {{$modalUser->firstName}} to login again.</div>
                            </label>
                        </div>
                        <div x-show="modalLock" class="note-danger">
                            {{$modalUser->firstName}} {{$modalUser->lastName}} will be blocked until {{$modalUser->firstName}}s account get unlocked again and will no longer be able to log
                            in again until then.
                        </div>
                    </div>
                    @endif
                </x-slot>

                <x-slot name="button">
                    <x-btn.primary type="submit">Logout</x-btn.primary>
                    <x-btn.secondary autofocus wire:click.prevent="closeLogoutUserModal">Cancel</x-btn.secondary>
                </x-slot>

            </x-modal.panel>

        </form>

    </x-modal>
</div>

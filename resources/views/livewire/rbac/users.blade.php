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
    <x-table class="table-fixed w-full">
        <x-slot name="head">
            <x-table.heading class="hidden md:table-cell" sortable multiColumn wire:click="sortBy('firstName')" :direction="$sorts['firstName'] ?? null">First name</x-table.heading>
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
                                <x-btn.link disabled="{{$user->id===auth()->id()}}" wire:click="loginAs('{{$user->id}}')">Login as</x-btn.link>
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
</div>

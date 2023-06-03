<x-menu.link request="dashboard" href="/dashboard">
    Dashboard
    <x-slot name="icon">
        <x-icon.home class="icon-menu" size="6"/>
    </x-slot>
</x-menu.link>

@canany(['viewAny'],\App\Models\FirewallRule::class)
    <x-menu.dropdown route="firewall*">
        <x-slot name="title">Firewall Management</x-slot>
        <x-slot name="icon">
            <x-icon.firewall class="icon-menu" size="6"/>
        </x-slot>
        @canany(['viewAny'],\App\Models\FirewallRule::class)
            <x-menu.dropdown-link request="firewall/requests/read" href="{{route('firewall.requests.read')}}">View Requests</x-menu.dropdown-link>
        @endcanany
        @canany(['serviceNow-firewallRequests-import'],[auth()->user()])
            <x-menu.dropdown-link request="firewall/requests/import" href="{{route('firewall.requests.import')}}">Import Requests</x-menu.dropdown-link>
        @endcanany
    </x-menu.dropdown>
@endcanany


@canany(['roles-readAll','operation-readAll','provider-readAll','group-read','group-readAll','user-readAll'])
    <x-menu.dropdown route="admin*">
        <x-slot name="title">Administration</x-slot>
        <x-slot name="icon">
            <x-icon.view-grid class="icon-menu" size="6"/>
        </x-slot>
        @canany(['user-read','user-readAll'],[auth()->user()])
        <x-menu.dropdown-link request="admin/users" href="{{route('admin.users')}}">Users</x-menu.dropdown-link>
        @endcanany
        @canany(['group-read','group-readAll'],[auth()->user()])
            <x-menu.dropdown-link request="admin/group" href="{{route('admin.group')}}">Groups</x-menu.dropdown-link>
        @endcanany
        @can('roles-readAll',[auth()->user()])
            <x-menu.dropdown-link request="admin/roles" href="{{route('admin.roles')}}">Roles</x-menu.dropdown-link>
        @endcan
        @can('operation-readAll',[auth()->user()])
            <x-menu.dropdown-link request="admin/operations" href="{{route('admin.operations')}}">Operations
            </x-menu.dropdown-link>
        @endcan
        @can('provider-readAll',[auth()->user()])
            <x-menu.dropdown-link request="admin/provider" href="{{route('admin.provider')}}">Provider
            </x-menu.dropdown-link>
        @endcan
        @can('viewTelescope',[auth()->user()])
            <x-menu.dropdown-link request="admin/telescope" target="_blank" href="/admin/telescope">Telescope
            </x-menu.dropdown-link>
        @endcan
    </x-menu.dropdown>
@endcanany
{{--
<x-menu.link request="foo" withIcon href="/foo">
    Account
    <x-slot name="icon">
        <x-icon.delete class="mr-3 md:mr-0 xl:mr-3 flex-shrink-0 text-lhg-gray-12 group-hover:text-lhg-gray-12 ease-in-out duration-300" size="6"/>
    </x-slot>
</x-menu.link>
--}}

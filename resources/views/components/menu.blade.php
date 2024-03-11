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


@can('admin-menu')
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
    </x-menu.dropdown>
@endcan
@canany(['mailhog-read','viewTelescope'])
    <x-menu.dropdown route="developer*">
        <x-slot name="title">Developers</x-slot>
        <x-slot name="icon">
            <x-icon.code class="icon-menu" size="6"/>
        </x-slot>
        @can('viewTelescope',[auth()->user()])
            <x-menu.dropdown-link request="developer/telescope" target="_blank" href="/admin/telescope">Telescope
            </x-menu.dropdown-link>
        @endcan
        @can('mailhog-read',[auth()->user()])
            @if (config('app.env') === 'local')
                <x-menu.dropdown-link request="developer/mailhog" target="_blank" href="http://localhost:8025/">Mailhog
                </x-menu.dropdown-link>
            @endif
            @if (config('app.env') === 'stage')
                <x-menu.dropdown-link request="developer/mailhog" target="_blank" href="/mailhog">Mailhog
                </x-menu.dropdown-link>
            @endif
        @endcan
        @if (config('app.env') === 'stage')
            @can('pma',[auth()->user()])
                <x-menu.dropdown-link request="developer/pma" target="_blank" href="/pma/index.php?route=/">PhpMyAdmin
                </x-menu.dropdown-link>
            @endcan
        @endif
        @if (config('app.env') === 'local')
            @can('pma-read',[auth()->user()])
                <x-menu.dropdown-link request="developer/pma" target="_blank" href="http://localhost:8080/">phpMyAdmin
                </x-menu.dropdown-link>
            @endcan
        @endif
    </x-menu.dropdown>
@endcan
{{--
<x-menu.link request="foo" withIcon href="/foo">
    Account
    <x-slot name="icon">
        <x-icon.delete class="mr-3 md:mr-0 xl:mr-3 flex-shrink-0 text-lhg-gray-12 group-hover:text-lhg-gray-12 ease-in-out duration-300" size="6"/>
    </x-slot>
</x-menu.link>
--}}

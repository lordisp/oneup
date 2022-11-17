<x-menu.link request="dashboard" href="/dashboard">
    Dashboard
    <x-slot name="icon">
        <x-icon.home class="icon-menu" size="6"/>
    </x-slot>
</x-menu.link>
@canany(['roles-readAll','operation-readAll','provider-readAll'])
    <x-menu.dropdown route="admin*">
        <x-slot name="title">Administration</x-slot>
        <x-slot name="icon">
            <x-icon.view-grid class="icon-menu" size="6"/>
        </x-slot>
        <x-menu.dropdown-link request="admin/users" href="{{route('admin.users')}}">Users</x-menu.dropdown-link>
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
@endcanany
{{--
<x-menu.link request="foo" withIcon href="/foo">
    Account
    <x-slot name="icon">
        <x-icon.delete class="mr-3 md:mr-0 xl:mr-3 flex-shrink-0 text-lhg-gray-12 group-hover:text-lhg-gray-12 ease-in-out duration-300" size="6"/>
    </x-slot>
</x-menu.link>
--}}

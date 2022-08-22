<x-menu.link request="dashboard" href="/dashboard">
    Dashboard
    <x-slot name="icon">
        <x-icon.home class="icon-menu" size="6"/>
    </x-slot>
</x-menu.link>

<x-menu.dropdown route="rbac*">
    <x-slot name="title">Administration</x-slot>
    <x-slot name="icon">
        <x-icon.view-grid class="icon-menu" size="6"/>
    </x-slot>
    <x-menu.dropdown-link request="admin/users" href="{{route('admin.users')}}">Users</x-menu.dropdown-link>
    <x-menu.dropdown-link request="admin/groups" href="#">Groups</x-menu.dropdown-link>
    <x-menu.dropdown-link request="admin/roles" href="#">Roles</x-menu.dropdown-link>
    <x-menu.dropdown-link request="admin/clients" href="#">Clients</x-menu.dropdown-link>
    <x-menu.dropdown-link request="admin/test" href="#">Test</x-menu.dropdown-link>
    <x-menu.dropdown-link request="admin/provider" href="{{route('admin.provider')}}">Provider</x-menu.dropdown-link>
</x-menu.dropdown>

{{--
<x-menu.link request="foo" withIcon href="/foo">
    Account
    <x-slot name="icon">
        <x-icon.delete class="mr-3 md:mr-0 xl:mr-3 flex-shrink-0 text-lhg-gray-12 group-hover:text-lhg-gray-12 ease-in-out duration-300" size="6"/>
    </x-slot>
</x-menu.link>
--}}

<a {{$attributes->merge(['class' => 'block px-4 py-2 text-sm text-gray-700 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-500 hover:text-gray-900'])}} role="menuitem" tabindex="-1" id="menu-item-0" @click="open = false">
    {{ $slot }}
</a>

<div x-show="open" class="fixed inset-0 flex z-40 md:hidden"
     x-description="Off-canvas menu for mobile, show/hide based on off-canvas menu state." x-ref="dialog"
     aria-modal="true">

    <div x-show="open" x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-600 bg-opacity-75"
         x-description="Off-canvas menu overlay, show/hide based on off-canvas menu state."
         @click="open = false" aria-hidden="true">
    </div>

    <div x-show="open" x-transition:enter="transition ease-in-out duration-300 transform"
         x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in-out duration-300 transform"
         x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
         x-description="Off-canvas menu, show/hide based on off-canvas menu state."
         class="relative flex-1 flex flex-col max-w-xs w-full pt-5 pb-4 bg-lhg-blue">

        <div x-show="open" x-transition:enter="ease-in-out duration-300"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in-out duration-300" x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             x-description="Close button, show/hide based on off-canvas menu state."
             class="absolute top-0 right-0 -mr-12 pt-2">
            <button type="button"
                    class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
                    @click="open = false">
                <span class="sr-only">Close sidebar</span>
                <x-icon.x size="6" class="text-white font-bold"/>
            </button>
        </div>

        <div class="flex-shrink-0 flex items-center px-4">
            <x-logo.oneup type="dark" class="h-8 w-auto"/>
        </div>
        <!-- Sidebar for mobile -->
        <div class="mt-5 flex-1  h-0 overflow-y-auto">
            <nav class="px-2 space-y-1">
                <x-menu/>
            </nav>
        </div>
        <x-menu.bottom/>
    </div>

    <div class="flex-shrink-0 w-14" aria-hidden="true">
        <!-- Dummy element to force sidebar to shrink to fit close icon -->
    </div>
</div>
<!-- Static sidebar for desktop -->
<div class="hidden xl:flex xl:w-64 xl:flex-col xl:fixed xl:inset-y-0">
    <x-sidebar.desktop>
        <x-menu/>
    </x-sidebar.desktop>
</div>
<div class="hidden xl:hidden md:w-28 md:flex md:flex-col md:fixed md:inset-y-0">
    <x-sidebar.narrow>
        <x-menu/>
    </x-sidebar.narrow>
</div>

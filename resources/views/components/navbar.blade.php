<nav class="sticky top-0 z-10 flex-shrink-0 flex h-16 bg-white dark:bg-gray-900 shadow dark:shadow-gray-600">
    <button type="button"
            class="px-4 border-r border-gray-200 dark:border-gray-700 text-gray-500 focus:outline-none focus:ring-0 focus:ring-inset  md:hidden"
            @click="open = true">
        <span class="sr-only">Open sidebar</span>
        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
             stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 6h16M4 12h16M4 18h7"></path>
        </svg>
    </button>
    <div class="flex-1 px-4 flex justify-between">
        <div class="flex-1 flex">
            <form class="invisible w-full flex md:ml-0 mb-0" action="#" method="GET">
                <label for="search-field" class="sr-only">Search</label>
                <div class="relative w-full text-gray-400 focus-within:text-gray-600">
                    <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none">
                        <x-icon.search size="5"/>
                    </div>
                    <input disabled id="search-field"
                           class="dark:bg-gray-900 block w-full h-full pl-8 pr-3 py-2 border-transparent text-gray-900 placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-0 focus:border-transparent text-sm"
                           placeholder="Search" type="search" name="search" autocomplete="off">
                </div>
            </form>
        </div>
        <div class="ml-4 flex items-center md:ml-6 space-x-4">
            <!-- Notifications -->
            <x-dropdown>
                <button x-ref="button"
                        x-on:click="toggle()"
                        :aria-expanded="open"
                        :aria-controls="$id('dropdown-button')"
                        type="button"
                        class="p-1 rounded-full dark:text-gray-300 dark:hover:text-gray-400 text-gray-400 hover:text-gray-500 focus:outline-none">
                    <span class="sr-only">View notifications</span>
                    <x-icon.bell size="6"/>
                </button>
                <x-dropdown.panel wide="w-96">
                    <div class="mx-3 my-1 text-xs text-lhg-text dark:text-gray-100">
                        Foo
                    </div>
                </x-dropdown.panel>
            </x-dropdown>
            <!-- Profile dropdown -->
            <x-dropdown>
                <!-- Button -->
                <x-dropdown.avatar/>
                <!-- Panel -->
                <x-dropdown.panel>
                    <x-dropdown.link href="#1">
                        Account settings
                    </x-dropdown.link>
                    <x-dropdown.link href="{{route('profile.clients')}}">
                        Clients
                    </x-dropdown.link>
                    <x-dropdown.link href="#3">
                        License
                    </x-dropdown.link>
                    <form class="m-0" method="POST" action="{{route('logout')}}" role="none">
                        @csrf
                        <x-dropdown.button>
                            Sign out {{auth()->user()->firstName}}
                        </x-dropdown.button>
                    </form>
                </x-dropdown.panel>
            </x-dropdown>
        </div>
    </div>
</nav>

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
        @if(session()->has('fromUser'))
            <livewire:admin.as-user/>
        @endif
        </div>
        <div class="ml-4 flex items-center md:ml-6 space-x-4">
            <!-- Notifications -->
            <button x-on:click="notifySlider = true" class="p-1 rounded-full dark:text-gray-300 dark:hover:text-gray-400 text-gray-400 hover:text-gray-500 focus:outline-none">
                <livewire:notification-bell/>
            </button>
            <!-- Profile dropdown -->
            <x-dropdown>
                <!-- Button -->
                <x-dropdown.avatar/>
                <!-- Panel -->
                <x-dropdown.panel>
                    <x-dropdown.link class="hidden" href="#1">
                        Account settings
                    </x-dropdown.link>
                    <x-dropdown.link class="hidden" href="{{route('profile.clients')}}">
                        Clients
                    </x-dropdown.link>
                    <x-dropdown.link class="hidden" href="#3">
                        License
                    </x-dropdown.link>
                    <form class="m-0" method="POST" action="{{route('logout')}}" role="none">
                        @csrf
                        <x-dropdown.button>
                            Sign out {{auth()->user()->firstName}}
                            <span class="text-xs">{{auth()->user()->email}}</span>
                        </x-dropdown.button>
                    </form>
                </x-dropdown.panel>
            </x-dropdown>
        </div>
    </div>
</nav>

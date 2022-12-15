<!-- Avatar -->
<button
    x-ref="button"
    x-on:click="toggle()"
    :aria-expanded="open"
    :aria-controls="$id('dropdown-button')"
    type="button"
    class="max-w-xs bg-white flex items-center text-sm rounded-full focus:outline-none border-2 border-gray-200 dark:border-gray-400"
>
            <span class="inline-block relative">
                <livewire:components.avatar class="h-8 w-8 rounded-full" :user-id="auth()->user()->email" :alt="auth()->user()->displayName"/>
            <span aria-hidden="true"
                  class=" hidden absolute bottom-0 right-0 block h-2.5 w-2.5 rounded-full ring-2 ring-white bg-green-500"></span>
            </span>
</button>

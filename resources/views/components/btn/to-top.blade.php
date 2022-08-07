<button
    x-cloak
    x-data="{scroll : false}"
    @scroll.window="document.documentElement.scrollTop > 20 ? scroll = true : scroll = false"
    x-show="scroll" @click="window.scrollTo({top: 0, behavior: 'smooth'})"
    x-transition:enter="transition-opacity ease-linear duration-500"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-linear duration-500"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    type="button"
    data-mdb-ripple="true"
    data-mdb-ripple-color="light"
    class="z-50 fixed inline-block p-3 text-xs font-bold leading-tight text-white uppercase transition duration-150 ease-in-out bg-lhg-blue dark:bg-lhg-yellow rounded-full shadow-xl hover:opacity-75 hover:shadow-lg focus:shadow-lg focus:outline-none focus:ring-0 active:bg-blue-800 active:shadow-lg bottom-20 right-3"
    id="btn-back-to-top"
>
    <x-icon.chevron-up solid />
</button>

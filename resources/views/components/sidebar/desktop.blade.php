<div class="flex flex-col flex-grow pt-5 bg-lhg-blue overflow-y-auto">
    <div class="flex items-center flex-shrink-0 px-4">
        <x-logo.oneup type="dark" class="h-8 w-auto"/>
    </div>
    <div class="mt-5 flex-1 flex flex-col">
        <nav class="flex-1 px-2 pb-4 space-y-1">
            {{ $slot }}
        </nav>
    </div>
    <x-menu.bottom/>
</div>

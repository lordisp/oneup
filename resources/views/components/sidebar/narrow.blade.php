<div class="hidden w-28 bg-lhg-blue justify-between overflow-y-auto md:flex flex-col flex-grow ">
    <div class="w-full py-6 flex flex-col items-center">
        <x-logo.oneup type="sm" class="h-8 w-auto"/>
        <div class="flex-1 mt-6 w-full px-2 space-y-1">
            {{$slot}}
        </div>
    </div>
    <x-menu.bottom/>
</div>

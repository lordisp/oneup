<div class="content">
    <x-title>
        {{__('Import Firewall Requests')}}
    </x-title>
    <x-card.gray class="rounded-lg">
        <x-form enctype="multipart/form-data">
            <x-card.fields>
                <x-slot name="title">Import Firewall-Rules</x-slot>
                <x-slot name="subtitle">Select one or more Json Exports from ServiceNow and click the import button.
                </x-slot>
                <x-card.form buttons class="w-full">
                    <div class="flex-1 min-w-0 space-y-2 sm:px-4 max-w-xl">
                        <x-label for="attachments" value="" class="mb-1"/>
                        <x-file-attachment
                                :file="$attachments"
                                accept="application/json"
                                wire:model="attachments"
                                multiple
                        />

                        <x-input-error for="attachments" class="mt-2"/>

                        <x-slot name="buttons">

                            <span wire:loading wire:target="save" class="animate-pulse">Loading...</span>
                            <x-btn.secondary
                                    wire:click.prevent="save" type="button"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="cursor-progress"
                            > Import
                            </x-btn.secondary>
                        </x-slot>
                    </div>
                </x-card.form>
            </x-card.fields>
        </x-form>
    </x-card.gray>
    {{--    @push('draggable')
            @vite('resources/js/json-form.js')
        @endpush--}}

</div>
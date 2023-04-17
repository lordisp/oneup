<div class="sm:hidden">
    <label for="tabs" class="sr-only">Select a tab</label>
    <x-input.select x-model="current" x-on:change="updateUrl()" id="tabs" name="tabs">
        {{$slot}}
    </x-input.select>
</div>
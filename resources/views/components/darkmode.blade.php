<!-- DarkMode Switch -->
@props(['size' => '8'])
<button x-cloak @click="darkMode = !darkMode ; open =false" type="button" class="focus:outline-none inline-flex p-1.5 rounded-full shadow-sm text-white">
    <span :class="{'hidden': darkMode === true}">
        <x-icon.sun solid size="{{ $size }}" class="text-lhg-yellow"/>
    </span>
    <span :class="{'hidden': darkMode === false}">
        <x-icon.moon solid size="{{ $size }}" class="text-lhg-yellow"/>
    </span>
</button>

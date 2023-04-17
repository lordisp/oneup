<div class="hidden sm:block pb-1">
    <nav aria-label="Tabs">
        <a @click.prevent="current='{{\Illuminate\Support\Str::lower($slot)}}';updateUrl()"
           :class="current === '{{\Illuminate\Support\Str::lower($slot)}}' ? active : inactive"
           :aria-selected="current === '{{\Illuminate\Support\Str::lower($slot)}}'"
           class="hover:text-gray-700 rounded-t-md px-3 py-2 outline-none text-sm font-medium"
           href="#"
        >{{$slot}}</a>
    </nav>
</div>
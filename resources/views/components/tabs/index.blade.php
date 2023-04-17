<div x-data="{
        current: '{{$default}}',
        updateUrl: function() {
            window.location.hash = '#' + this.current;
        },
        active:'bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-300',
        inactive:'text-gray-500 hover:text-gray-700 dark:text-gray-200 dark:hover:text-gray-300 transition duration-300',
        }"
>
    {{$slot}}
</div>
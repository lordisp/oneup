<div {{$attributes->merge(['class'=>''])}}>
    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{$title}}</dt>
    <dd class="mt-1 text-sm text-gray-900 dark:text-white">
        {{$slot}}
    </dd>
</div>
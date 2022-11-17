@props(['subtitle' => false])
<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ $title }}
        </h3>
        @if($subtitle)
        <p class="mt-1 max-w-2xl text-sm text-gray-500">
            {{ $subtitle }}
        </p>
        @endif
    </div>
    <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
        <dl class="sm:divide-y sm:divide-gray-200">
            {{ $content }}
        </dl>
    </div>
</div>
